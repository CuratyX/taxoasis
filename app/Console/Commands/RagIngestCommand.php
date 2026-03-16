<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RagService;

/**
 * Artisan command to ingest tax documents into the RAG vector store.
 *
 * Usage:
 *   php artisan rag:ingest /path/to/document.pdf "FTA Corporate Tax Guide 2024"
 *   php artisan rag:ingest /path/to/folder --source "FTA Documents 2024"
 *   php artisan rag:list
 *   php artisan rag:remove "FTA Corporate Tax Guide 2024"
 */
class RagIngestCommand extends Command
{
    protected $signature = 'rag:ingest
        {path : Path to a file or directory of documents}
        {--source= : Human-readable source name (defaults to filename)}
        {--list : List all ingested sources and their chunk counts}
        {--remove= : Remove all chunks from a specific source}';

    protected $description = 'Ingest tax documents into the RAG vector store for AI-powered retrieval';

    protected RagService $rag;

    public function __construct(RagService $rag)
    {
        parent::__construct();
        $this->rag = $rag;
    }

    public function handle(): int
    {
        // ── List mode ────────────────────────────
        if ($this->option('list')) {
            return $this->listSources();
        }

        // ── Remove mode ──────────────────────────
        $removeSource = $this->option('remove');
        if ($removeSource) {
            return $this->removeSource($removeSource);
        }

        // ── Ingest mode ──────────────────────────
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("Path not found: {$path}");
            return 1;
        }

        if (is_dir($path)) {
            return $this->ingestDirectory($path);
        }

        return $this->ingestFile($path);
    }

    /**
     * Ingest a single file.
     */
    protected function ingestFile(string $filePath): int
    {
        $sourceName = $this->option('source') ?? pathinfo($filePath, PATHINFO_FILENAME);

        $this->info("Ingesting: {$filePath}");
        $this->info("Source name: {$sourceName}");

        try {
            $chunkCount = $this->rag->ingestDocument($filePath, $sourceName);
            $this->info("Successfully ingested {$chunkCount} chunks from: {$sourceName}");
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to ingest: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Ingest all supported files in a directory.
     */
    protected function ingestDirectory(string $dirPath): int
    {
        $supportedExtensions = ['pdf', 'txt', 'md', 'docx'];
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $supportedExtensions)) {
                $files[] = $file->getPathname();
            }
        }

        if (empty($files)) {
            $this->warn("No supported documents found in: {$dirPath}");
            $this->info("Supported formats: " . implode(', ', $supportedExtensions));
            return 1;
        }

        $this->info("Found " . count($files) . " documents to ingest.");

        $totalChunks = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $filePath) {
            $sourceName = $this->option('source')
                ? $this->option('source') . ' — ' . pathinfo($filePath, PATHINFO_FILENAME)
                : pathinfo($filePath, PATHINFO_FILENAME);

            try {
                $chunkCount = $this->rag->ingestDocument($filePath, $sourceName);
                $totalChunks += $chunkCount;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->warn("  Skipped {$filePath}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Ingestion complete: {$totalChunks} total chunks from " . (count($files) - $errors) . " files.");

        if ($errors > 0) {
            $this->warn("{$errors} file(s) failed to ingest.");
        }

        return 0;
    }

    /**
     * List all ingested sources.
     */
    protected function listSources(): int
    {
        $sources = $this->rag->listSources();

        if (empty($sources)) {
            $this->info("No documents have been ingested yet.");
            $this->info("Use: php artisan rag:ingest /path/to/document.pdf");
            return 0;
        }

        $this->info("Ingested RAG Sources:");
        $this->info(str_repeat('─', 60));

        $total = 0;
        foreach ($sources as $name => $count) {
            $this->line("  {$name}: {$count} chunks");
            $total += $count;
        }

        $this->info(str_repeat('─', 60));
        $this->info("Total: " . count($sources) . " sources, {$total} chunks");

        return 0;
    }

    /**
     * Remove a source from the store.
     */
    protected function removeSource(string $sourceName): int
    {
        if (!$this->confirm("Remove all chunks from source '{$sourceName}'?")) {
            $this->info('Cancelled.');
            return 0;
        }

        $removed = $this->rag->removeSource($sourceName);
        $this->info("Removed {$removed} chunks from: {$sourceName}");

        return 0;
    }
}
