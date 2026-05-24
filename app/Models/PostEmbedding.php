<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostEmbedding extends Model
{
    protected $fillable = [
        'post_id',
        'embedding',
        'embedding_model',
        'indexed_at',
    ];

    protected $casts = [
        'embedding' => 'array',
        'indexed_at' => 'datetime',
    ];

    /**
     * Get post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Check if embedding is stale
     */
    public function isStale(int $days = 30): bool
    {
        if (!$this->indexed_at) {
            return true;
        }

        return $this->indexed_at->diffInDays(now()) > $days;
    }

    /**
     * Reindex this embedding
     */
    public function reindex(): bool
    {
        $service = app(\App\Search\EmbeddingService::class);
        $content = $service->buildSearchableContent($this->post);

        try {
            $embedding = $service->embed($content);

            $this->update([
                'embedding' => $embedding,
                'embedding_model' => $service->getProviderInfo()['model'],
                'indexed_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
