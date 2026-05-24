<?php

namespace App\Console\Commands;

use App\Models\SourceRecord;
use App\Services\AiServiceFactory;
use App\Services\NormalizeService;
use Illuminate\Console\Command;

class ExtractAiCommand extends Command
{
    protected $signature = 'ai:extract 
                            {--record= : Process a specific record ID}
                            {--batch= : Process all records in a batch ID}
                            {--pending : Process all pending records}
                            {--provider= : Override AI provider (ollama|gemini)}
                            {--limit= : Limit number of records to process}
                            {--test : Run test extraction}';

    protected $description = 'Extract information from source records using AI';

    public function handle(): int
    {
        $provider = $this->option('provider');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($this->option('test')) {
            return $this->testExtraction($provider);
        }

        if ($recordId = $this->option('record')) {
            return $this->processRecord((int) $recordId, $provider);
        }

        if ($batchId = $this->option('batch')) {
            return $this->processBatch((int) $batchId, $provider, $limit);
        }

        if ($this->option('pending')) {
            return $this->processPending($provider, $limit);
        }

        $this->error('Please specify an option: --test, --record=ID, --batch=ID, or --pending');
        return self::FAILURE;
    }

    private function testExtraction(?string $provider): int
    {
        $this->info('🧪 Running AI extraction test...');
        $this->newLine();

        $testContent = "Bán đất 900tr, mặt tiền 8m x 18m, ngõ 775 Tân Lập, Thái Nguyên. Liên hệ 0988123456";

        $this->info("Content:\n{$testContent}");
        $this->newLine();

        $startTime = microtime(true);
        $result = AiServiceFactory::extract($testContent, $provider);
        $elapsed = round((microtime(true) - $startTime) * 1000);

        if (!$result['success']) {
            $this->error("❌ Extraction failed: " . ($result['error'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        $this->info("✅ Extraction successful ({$elapsed}ms)");
        $this->info("Provider: " . ($result['provider'] ?? 'unknown'));
        $this->info("Model: " . ($result['model'] ?? 'unknown'));
        $this->newLine();

        $this->info('📋 Extracted data:');
        $this->line(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info('📊 Normalized data:');

        $normalized = app(NormalizeService::class)->normalize($result['data']);
        $this->line(json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function processRecord(int $recordId, ?string $provider): int
    {
        $record = SourceRecord::find($recordId);

        if (!$record) {
            $this->error("Record #{$recordId} not found");
            return self::FAILURE;
        }

        $this->info("Processing record #{$recordId}...");

        $result = AiServiceFactory::extract($record->raw_content, $provider);

        if (!$result['success']) {
            $record->update([
                'status' => 'failed',
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);
            $this->error("❌ Failed: " . ($result['error'] ?? 'Unknown error'));
            return self::FAILURE;
        }

        $record->update(['status' => 'completed']);
        $this->info("✅ Record #{$recordId} processed successfully");

        return self::SUCCESS;
    }

    private function processBatch(int $batchId, ?string $provider, ?int $limit): int
    {
        $records = SourceRecord::where('import_batch_id', $batchId)
            ->where('status', 'pending')
            ->when($limit, fn($q) => $q->limit($limit))
            ->get();

        if ($records->isEmpty()) {
            $this->warn("No pending records found in batch #{$batchId}");
            return self::SUCCESS;
        }

        $this->info("Processing {$records->count()} records from batch #{$batchId}...");
        $this->newLine();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($records as $record) {
            $result = AiServiceFactory::extract($record->raw_content, $provider);

            if ($result['success']) {
                $record->update(['status' => 'completed']);
                $success++;
            } else {
                $record->update([
                    'status' => 'failed',
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Completed: {$success} | ❌ Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function processPending(?string $provider, ?int $limit): int
    {
        $query = SourceRecord::where('status', 'pending');

        if ($limit) {
            $query->limit($limit);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            $this->warn("No pending records found");
            return self::SUCCESS;
        }

        $this->info("Processing {$records->count()} pending records...");
        $this->newLine();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($records as $record) {
            $result = AiServiceFactory::extract($record->raw_content, $provider);

            if ($result['success']) {
                $record->update(['status' => 'completed']);
                $success++;
            } else {
                $record->update([
                    'status' => 'failed',
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Completed: {$success} | ❌ Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
