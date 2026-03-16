<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

/**
 * RAG (Retrieval-Augmented Generation) Service for TaxOasis.
 *
 * This service handles:
 * 1. Ingesting PDF/text documents from the FTA and other official sources
 * 2. Chunking documents into manageable pieces
 * 3. Generating embeddings via Anthropic's Voyage (or OpenAI) embeddings API
 * 4. Storing embeddings in a JSON-based flat file store (phase 1) or MySQL (phase 2)
 * 5. Retrieving the most relevant chunks for a given user query
 *
 * ARCHITECTURE DECISION:
 * Phase 1 (launch): JSON flat-file storage with cosine similarity in PHP.
 *   - Suitable for up to ~500 document chunks (plenty for FTA documents).
 *   - No additional database or service dependencies.
 *   - Cached in memory for fast retrieval.
 *
 * Phase 2 (scale): Migrate to MySQL with a dedicated `document_chunks` table
 *   and compute similarity in SQL, or upgrade to pgvector/Pinecone.
 */
class RagService
{
    protected string $embeddingApiKey;
    protected string $embeddingModel = 'text-embedding-3-small'; // OpenAI — cost-effective, 1536 dims
    protected string $embeddingBaseUrl = 'https://api.openai.com/v1/embeddings';

    /**
     * Path to the vector store JSON file.
     * Structure: [{ "id": str, "source": str, "content": str, "embedding": float[] }, ...]
     */
    protected string $storePath;

    /**
     * Maximum chunk size in characters (~400 tokens).
     * Tax documents benefit from larger chunks to preserve regulatory context.
     */
    protected int $chunkSize = 1500;

    /**
     * Overlap between chunks to avoid splitting mid-sentence on a critical provision.
     */
    protected int $chunkOverlap = 200;

    /**
     * Number of top chunks to return for context.
     */
    protected int $topK = 5;

    /**
     * Minimum similarity score to include a chunk (0–1 scale).
     */
    protected float $similarityThreshold = 0.3;

    public function __construct()
    {
        $this->embeddingApiKey = config('services.openai.api_key', '');
        $this->storePath = storage_path('app/rag/vector_store.json');
    }

    // ─────────────────────────────────────────────
    // RETRIEVAL — Called on every user query
    // ─────────────────────────────────────────────

    /**
     * Retrieve the most relevant document chunks for a user's tax question.
     *
     * @param string $query  The user's natural language question
     * @return string        Concatenated context string for the system prompt, or empty if no RAG data
     */
    public function retrieveContext(string $query): string
    {
        // If no embedding API key or no vector store, gracefully return empty
        if (empty($this->embeddingApiKey) || !file_exists($this->storePath)) {
            return '';
        }

        try {
            $queryEmbedding = $this->getEmbedding($query);
            $store = $this->loadStore();

            if (empty($store)) {
                return '';
            }

            // Calculate cosine similarity against all stored chunks
            $scored = [];
            foreach ($store as $chunk) {
                $similarity = $this->cosineSimilarity($queryEmbedding, $chunk['embedding']);
                if ($similarity >= $this->similarityThreshold) {
                    $scored[] = [
                        'content' => $chunk['content'],
                        'source' => $chunk['source'],
                        'score' => $similarity,
                    ];
                }
            }

            // Sort by descending similarity
            usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

            // Take top K
            $topChunks = array_slice($scored, 0, $this->topK);

            if (empty($topChunks)) {
                return '';
            }

            // Format as context string
            $context = '';
            foreach ($topChunks as $i => $chunk) {
                $num = $i + 1;
                $context .= "[Document {$num}: {$chunk['source']} | Relevance: " . round($chunk['score'], 2) . "]\n";
                $context .= $chunk['content'] . "\n\n";
            }

            return $context;

        } catch (\Exception $e) {
            Log::warning('RAG retrieval failed, proceeding without context', [
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    // ─────────────────────────────────────────────
    // INGESTION — Called via artisan command
    // ─────────────────────────────────────────────

    /**
     * Ingest a document into the vector store.
     *
     * @param string $filePath   Absolute path to the document file
     * @param string $sourceName Human-readable source name (e.g., "FTA CT Guide 2024")
     * @return int               Number of chunks created
     */
    public function ingestDocument(string $filePath, string $sourceName): int
    {
        if (empty($this->embeddingApiKey)) {
            throw new \RuntimeException('Embedding API key not configured. Set OPENAI_API_KEY in .env');
        }

        // Extract text based on file type
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

        // Chunk the text
        $chunks = $this->chunkText($text);

        Log::info("Chunked document into " . count($chunks) . " pieces", ['source' => $sourceName]);

        // Generate embeddings for all chunks (batched)
        $embeddings = $this->getEmbeddingsBatch(array_column($chunks, 'content'));

        // Build store entries
        $entries = [];
        foreach ($chunks as $i => $chunk) {
            $entries[] = [
                'id' => md5($sourceName . '_' . $i),
                'source' => $sourceName,
                'content' => $chunk['content'],
                'embedding' => $embeddings[$i],
            ];
        }

        // Merge into existing store (replace entries from same source)
        $this->mergeIntoStore($entries, $sourceName);

        return count($entries);
    }

    /**
     * Remove all chunks from a specific source.
     */
    public function removeSource(string $sourceName): int
    {
        $store = $this->loadStore();
        $originalCount = count($store);

        $store = array_values(array_filter(
            $store,
            fn($entry) => $entry['source'] !== $sourceName
        ));

        $this->saveStore($store);
        $removed = $originalCount - count($store);

        Log::info("Removed {$removed} chunks from source: {$sourceName}");
        return $removed;
    }

    /**
     * List all ingested sources and their chunk counts.
     */
    public function listSources(): array
    {
        $store = $this->loadStore();
        $sources = [];

        foreach ($store as $entry) {
            $source = $entry['source'];
            if (!isset($sources[$source])) {
                $sources[$source] = 0;
            }
            $sources[$source]++;
        }

        return $sources;
    }

    // ─────────────────────────────────────────────
    // TEXT EXTRACTION
    // ─────────────────────────────────────────────

    /**
     * Extract text from a PDF using pdftotext (poppler-utils).
     */
    protected function extractTextFromPdf(string $filePath): string
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'rag_pdf_');

        // Try pdftotext first (best quality)
        $command = sprintf(
            'pdftotext -layout %s %s 2>&1',
            escapeshellarg($filePath),
            escapeshellarg($outputFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputFile)) {
            $text = file_get_contents($outputFile);
            unlink($outputFile);
            return $text;
        }

        // Fallback: try with php (basic extraction)
        unlink($outputFile);

        // If pdftotext is not available, try a PHP-based approach
        // This requires smalot/pdfparser: composer require smalot/pdfparser
        if (class_exists('\Smalot\PdfParser\Parser')) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        throw new \RuntimeException(
            "Cannot extract text from PDF. Install poppler-utils (apt install poppler-utils) "
            . "or smalot/pdfparser (composer require smalot/pdfparser)."
        );
    }

    /**
     * Extract text from a DOCX file.
     */
    protected function extractTextFromDocx(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException("Cannot open DOCX file: {$filePath}");
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \RuntimeException("Cannot read document.xml from DOCX");
        }

        // Strip XML tags, preserving paragraph breaks
        $xml = str_replace('</w:p>', "\n", $xml);
        $text = strip_tags($xml);

        return $text;
    }

    // ─────────────────────────────────────────────
    // CHUNKING
    // ─────────────────────────────────────────────

    /**
     * Split text into overlapping chunks, respecting sentence boundaries.
     *
     * @return array<array{content: string}>
     */
    protected function chunkText(string $text): array
    {
        // Normalise whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Split into sentences (rough but effective)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $potentialLength = strlen($currentChunk) + strlen($sentence) + 1;

            if ($potentialLength > $this->chunkSize && !empty($currentChunk)) {
                $chunks[] = ['content' => trim($currentChunk)];

                // Create overlap: take the last portion of the current chunk
                $words = explode(' ', $currentChunk);
                $overlapWords = array_slice($words, -intval($this->chunkOverlap / 5)); // ~5 chars per word
                $currentChunk = implode(' ', $overlapWords) . ' ' . $sentence;
            } else {
                $currentChunk .= ' ' . $sentence;
            }
        }

        // Don't forget the last chunk
        if (!empty(trim($currentChunk))) {
            $chunks[] = ['content' => trim($currentChunk)];
        }

        return $chunks;
    }

    // ─────────────────────────────────────────────
    // EMBEDDINGS
    // ─────────────────────────────────────────────

    /**
     * Get embedding for a single text (used for queries).
     */
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

            if ($response->failed()) {
                throw new \Exception('Embedding API failed: ' . $response->body());
            }

            return $response->json('data.0.embedding');
        });
    }

    /**
     * Get embeddings for multiple texts (used during ingestion).
     * Batches to stay within API limits.
     */
    protected function getEmbeddingsBatch(array $texts): array
    {
        $batchSize = 20; // OpenAI allows up to 2048, but we keep it moderate
        $allEmbeddings = [];

        foreach (array_chunk($texts, $batchSize) as $batch) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->embeddingApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->embeddingBaseUrl, [
                'model' => $this->embeddingModel,
                'input' => $batch,
            ]);

            if ($response->failed()) {
                throw new \Exception('Embedding batch API failed: ' . $response->body());
            }

            $data = $response->json('data');
            foreach ($data as $item) {
                $allEmbeddings[] = $item['embedding'];
            }

            // Small delay between batches to respect rate limits
            if (count($texts) > $batchSize) {
                usleep(200000); // 200ms
            }
        }

        return $allEmbeddings;
    }

    // ─────────────────────────────────────────────
    // VECTOR MATH
    // ─────────────────────────────────────────────

    /**
     * Calculate cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    // ─────────────────────────────────────────────
    // STORAGE (JSON flat-file, Phase 1)
    // ─────────────────────────────────────────────

    /**
     * Load the vector store from disk.
     * Cached in memory for the duration of the request.
     */
    protected function loadStore(): array
    {
        return Cache::remember('rag_vector_store', 300, function () {
            if (!file_exists($this->storePath)) {
                return [];
            }

            $json = file_get_contents($this->storePath);
            return json_decode($json, true) ?? [];
        });
    }

    /**
     * Save the vector store to disk and invalidate cache.
     */
    protected function saveStore(array $store): void
    {
        $dir = dirname($this->storePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->storePath,
            json_encode($store, JSON_UNESCAPED_UNICODE)
        );

        Cache::forget('rag_vector_store');
    }

    /**
     * Merge new entries into the store, replacing any existing entries from the same source.
     */
    protected function mergeIntoStore(array $newEntries, string $sourceName): void
    {
        $store = $this->loadStore();

        // Remove existing entries from this source
        $store = array_values(array_filter(
            $store,
            fn($entry) => $entry['source'] !== $sourceName
        ));

        // Add new entries
        $store = array_merge($store, $newEntries);

        $this->saveStore($store);
    }
}
