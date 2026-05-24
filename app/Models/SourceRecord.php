<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceRecord extends Model
{
    protected $fillable = [
        'import_batch_id',
        'post_id',
        'raw_content',
        'raw_content_hash',
        'raw_json',
        'status',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'raw_json' => 'array',
        'retry_count' => 'integer',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(Post $post): void
    {
        $this->update([
            'status' => 'completed',
            'post_id' => $post->id,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'status' => 'skipped',
            'error_message' => $reason,
        ]);
    }
}
