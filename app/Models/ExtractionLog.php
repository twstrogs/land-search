<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractionLog extends Model
{
    protected $fillable = [
        'post_id',
        'prompt_version_id',
        'raw_content',
        'raw_response',
        'normalized_data',
        'confidence_score',
        'processing_time_ms',
        'validation_status',
        'validation_errors',
        'ai_provider',
        'ai_model',
        'metadata',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'normalized_data' => 'array',
        'confidence_score' => 'decimal:4',
        'validation_errors' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get prompt version
     */
    public function promptVersion(): BelongsTo
    {
        return $this->belongsTo(PromptVersion::class, 'prompt_version_id');
    }

    /**
     * Scope to failed validations
     */
    public function scopeFailed($query)
    {
        return $query->where('validation_status', 'failed');
    }

    /**
     * Scope to passed validations
     */
    public function scopePassed($query)
    {
        return $query->where('validation_status', 'passed');
    }

    /**
     * Scope to low confidence
     */
    public function scopeLowConfidence($query, float $threshold = 0.7)
    {
        return $query->where('confidence_score', '<', $threshold);
    }

    /**
     * Check if extraction was successful
     */
    public function isSuccessful(): bool
    {
        return $this->validation_status === 'passed' 
            && !empty($this->normalized_data);
    }

    /**
     * Get error summary
     */
    public function getErrorSummary(): ?string
    {
        $errors = $this->validation_errors ?? [];
        
        if (empty($errors)) {
            return null;
        }

        return implode('; ', array_column($errors, 'message'));
    }

    /**
     * Log a new extraction
     */
    public static function log(
        string $rawContent,
        array $rawResponse,
        array $normalizedData,
        float $confidence,
        int $processingTime,
        string $provider,
        string $model,
        ?int $postId = null,
        ?int $promptVersionId = null
    ): self {
        return self::create([
            'post_id' => $postId,
            'prompt_version_id' => $promptVersionId,
            'raw_content' => $rawContent,
            'raw_response' => $rawResponse,
            'normalized_data' => $normalizedData,
            'confidence_score' => $confidence,
            'processing_time_ms' => $processingTime,
            'ai_provider' => $provider,
            'ai_model' => $model,
            'validation_status' => 'pending',
        ]);
    }

    /**
     * Get recent statistics
     */
    public static function getRecentStats(int $days = 7): array
    {
        $since = now()->subDays($days);

        $stats = self::where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total,
                AVG(confidence_score) as avg_confidence,
                SUM(processing_time_ms) as total_time_ms,
                MAX(processing_time_ms) as max_time_ms,
                MIN(processing_time_ms) as min_time_ms
            ')
            ->first();

        $statusCounts = self::where('created_at', '>=', $since)
            ->selectRaw('validation_status, COUNT(*) as count')
            ->groupBy('validation_status')
            ->pluck('count', 'validation_status')
            ->toArray();

        return [
            'total' => (int) $stats->total,
            'avg_confidence' => round($stats->avg_confidence ?? 0, 4),
            'avg_time_ms' => $stats->total_time_ms > 0 
                ? round($stats->total_time_ms / max($stats->total, 1)) 
                : 0,
            'max_time_ms' => (int) $stats->max_time_ms,
            'min_time_ms' => (int) $stats->min_time_ms,
            'status_counts' => $statusCounts,
        ];
    }
}
