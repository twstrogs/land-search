<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use App\Services\AI\AiServiceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 7200;

    public function __construct(
        public ImportBatch $batch,
        public array $data
    ) {}

    public function handle(): void
    {
        $this->batch->update(['status' => 'processing']);

        $records = is_array($this->data) ? $this->data : [];

        foreach ($records as $index => $record) {
            ProcessSourceRecordJob::dispatch($this->batch, $record);
        }

        if ($this->batch->processed_records === 0 && $this->batch->failed_records === 0) {
            $this->batch->update([
                'status' => 'completed',
            ]);
        }
    }
}
