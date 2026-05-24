<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'source',
        'ai_provider',
        'started_at',
        'total_records',
        'processed_records',
        'failed_records',
        'skipped_records',
        'duplicate_records',
        'status',
        'error_message',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'failed_records' => 'integer',
        'skipped_records' => 'integer',
        'duplicate_records' => 'integer',
        'started_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceRecords(): HasMany
    {
        return $this->hasMany(SourceRecord::class);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_records === 0) {
            return 0;
        }
        $done = $this->processed_records + $this->failed_records + $this->skipped_records + $this->duplicate_records;
        return round(($done / $this->total_records) * 100, 1);
    }
    
    public function getCurrentIndexAttribute(): int
    {
        return $this->processed_records + $this->failed_records + $this->skipped_records + $this->duplicate_records;
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->ai_provider) {
            'ollama' => 'Ollama (Local)',
            'gemini' => 'Google Gemini',
            default => 'Unknown',
        };
    }

    public function syncComputedStatus(): void
    {
        $done = $this->processed_records + $this->failed_records + $this->skipped_records + $this->duplicate_records;

        if ($this->total_records > 0 && $done >= $this->total_records && $this->status !== 'completed') {
            $this->update(['status' => 'completed']);
            return;
        }

        if ($done < $this->total_records && $this->status === 'pending') {
            $this->update(['status' => 'processing']);
        }
    }
}
