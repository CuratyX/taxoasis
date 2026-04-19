<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RagService
{
    protected string $embeddingApiKey;
    protected string $embeddingModel = 'text-embedding-3-small';
    protected string $embeddingBaseUrl = 'https://api.openai.com/v1/embeddings';

    protected int $chunkSize = 1500;
    protected int $chunkOverlap = 200;
    protected int $topK = 5;
    protected float $similarityThreshold = 0.3;

    public function __construct()
    {
        $this->embeddingApiKey = config('services.openai.api_key', '');
    }

    // ─────────────────────────────────────────────
    // RETRIEVAL (MySQL Powered)
    // ─────────────────────────────────────────────
    public function retrieveContext(string $query): string
    {
        if (empty($this->embeddingApiKey)) return '';

        try {
            $queryEmbedding = $this->getEmbedding($query);
            $store = DB::table('rag_chunks')->get();

            if ($store->isEmpty()) return '';

            $scored = [];
            foreach ($store as $row) {
                $chunkEmbedding = json_decode($row->embedding, true);
                $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);

                if ($similarity >= $this->similarityThreshold) {
                    $scored[] = [
                        'content' => $row->content,
                        'source' => $row->source_name,
                        'score' => $similarity,
                    ];
                }
            }

            usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
            $topChunks = array_slice($scored, 0, $this->topK);

            if (empty($topChunks)) return '';

            $context = '';
            foreach ($topChunks as $i => $chunk) {
                $num = $i + 1;
                $context .= "[Document {$num}: {$chunk['source']} | Relevance: " . round($chunk['score'], 2) . "]\n";
                $context .= $chunk['content'] . "\n\n";
            }
            return $context;

        } catch (\Exception $e) {
            Log::warning('RAG retrieval failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    // ─────────────────────────────────────────────
    // INGESTION
    // ─────────────────────────────────────────────
    public function ingestDocument(string $filePath, string $sourceName): int
    {
        if (empty($this->embeddingApiKey)) {
            throw new \RuntimeException('Embedding API key not configured. Set OPENAI_API_KEY in .env');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $text = match ($extension) {
            'pdf' => $this->extractTextFromPdf($filePath),
            'txt', 'md' => file_get_contents($filePath),
            'docx' => $this->extractTextFromDocx($filePath),
            default => throw new \InvalidArgumentException("Unsupported file type: {$extension}"),
        };

        if (empty(trim($text))) {
            throw new \RuntimeException("No text could be extracted from: {$filePath}");
        }

        $chunks = $this->chunkText($text);
        $embeddings = $this->getEmbeddingsBatch(array_column($chunks, 'content'));

        $entries = [];
        foreach ($chunks as $i => $chunk) {
            $entries[] = [
                'source' => $sourceName,
                'content' => $chunk['content'],
                'embedding' => $embeddings[$i],
            ];
        }

        $this->mergeIntoStore($entries, $sourceName);

        return count($entries);
    }

    public function removeSource(string $sourceName): int
    {
        $deleted = DB::table('rag_chunks')->where('source_name', $sourceName)->delete();
        Log::info("Removed {$deleted} chunks from source: {$sourceName}");
        return $deleted;
    }

    public function listSources(): array
    {
        return DB::table('rag_chunks')
            ->select('source_name', DB::raw('count(*) as total'))
            ->groupBy('source_name')
            ->pluck('total', 'source_name')
            ->toArray();
    }

    // ─────────────────────────────────────────────
    // STORAGE (MySQL Powered)
    // ─────────────────────────────────────────────
    protected function mergeIntoStore(array $newEntries, string $sourceName): void
    {
        // 1. Remove old chunks for this document to prevent duplicates
        DB::table('rag_chunks')->where('source_name', $sourceName)->delete();

        // 2. Prepare the data
        $insertData = [];
        foreach ($newEntries as $entry) {
            $insertData[] = [
                'source_name' => $entry['source'],
                'content' => $entry['content'],
                'embedding' => json_encode($entry['embedding']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 3. Insert in batches of 50 to prevent MySQL overload
        foreach (array_chunk($insertData, 50) as $batch) {
            DB::table('rag_chunks')->insert($batch);
        }
    }

    // ─────────────────────────────────────────────
    // TEXT EXTRACTION
    // ─────────────────────────────────────────────
    protected function extractTextFromPdf(string $filePath): string
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'rag_pdf_');
        $command = sprintf('pdftotext -layout %s %s 2>&1', escapeshellarg($filePath), escapeshellarg($outputFile));
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputFile)) {
            $text = file_get_contents($outputFile);
            unlink($outputFile);
            return $text;
        }

        unlink($outputFile);

        if (class_exists('\Smalot\PdfParser\Parser')) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        throw new \RuntimeException("Cannot extract text from PDF. Install smalot/pdfparser via composer.");
    }

    protected function extractTextFromDocx(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) throw new \RuntimeException("Cannot open DOCX file: {$filePath}");

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false) throw new \RuntimeException("Cannot read document.xml from DOCX");

        $xml = str_replace('</w:p>', "\n", $xml);
        return strip_tags($xml);
    }

    // ─────────────────────────────────────────────
    // CHUNKING
    // ─────────────────────────────────────────────
    protected function chunkText(string $text): array
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $potentialLength = strlen($currentChunk) + strlen($sentence) + 1;

            if ($potentialLength > $this->chunkSize && !empty($currentChunk)) {
                $chunks[] = ['content' => trim($currentChunk)];
                $words = explode(' ', $currentChunk);
                $overlapWords = array_slice($words, -intval($this->chunkOverlap / 5));
                $currentChunk = implode(' ', $overlapWords) . ' ' . $sentence;
            } else {
                $currentChunk .= ' ' . $sentence;
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = ['content' => trim($currentChunk)];
        }

        return $chunks;
    }

    // ─────────────────────────────────────────────
    // EMBEDDINGS & MATH
    // ─────────────────────────────────────────────
    protected function getEmbedding(string $text): array
    {
        $cacheKey = 'emb_' . md5($text);

        return Cache::remember($cacheKey, 3600, function () use ($text) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->embeddingApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->embeddingBaseUrl, [
                'model' => $this->embeddingModel,
                'input' => $text,
            ]);

            if ($response->failed()) throw new \Exception('Embedding API failed: ' . $response->body());
            return $response->json('data.0.embedding');
        });
    }

    protected function getEmbeddingsBatch(array $texts): array
    {
        $batchSize = 20;
        $allEmbeddings = [];

        foreach (array_chunk($texts, $batchSize) as $batch) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->embeddingApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->embeddingBaseUrl, [
                'model' => $this->embeddingModel,
                'input' => $batch,
            ]);

            if ($response->failed()) throw new \Exception('Embedding batch API failed: ' . $response->body());

            $data = $response->json('data');
            foreach ($data as $item) {
                $allEmbeddings[] = $item['embedding'];
            }

            if (count($texts) > $batchSize) usleep(200000);
        }

        return $allEmbeddings;
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0; $normA = 0.0; $normB = 0.0;
        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) return 0.0;
        return $dotProduct / ($normA * $normB);
    }
}
