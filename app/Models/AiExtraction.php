<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExtraction extends Model
{
    protected $fillable = [
        'post_id',
        'source_record_id',
        'raw_response',
        'normalized_data',
        'confidence',
        'tokens_used',
        'processing_time_ms',
        'error_message',
        'ai_provider',
        'ai_model',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'normalized_data' => 'array',
        'confidence' => 'decimal:4',
        'tokens_used' => 'integer',
        'processing_time_ms' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(SourceRecord::class);
    }
}
