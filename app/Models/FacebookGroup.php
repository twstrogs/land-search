<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'enabled',
        'scrape_limit',
        'priority',
        'status',
        'last_error',
        'last_scrape_at',
        'posts_scraped',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'scrape_limit' => 'integer',
        'priority' => 'integer',
        'last_scrape_at' => 'datetime',
        'posts_scraped' => 'integer',
    ];

    public const STATUS_IDLE = 'idle';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function scrapeLogs(): HasMany
    {
        return $this->hasMany(ScrapeLog::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function markAsRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'last_error' => null,
        ]);
    }

    public function markAsCompleted(int $postsScraped): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'last_scrape_at' => now(),
            'posts_scraped' => $this->posts_scraped + $postsScraped,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
        ]);
    }

    public function reset(): void
    {
        $this->update([
            'status' => self::STATUS_IDLE,
            'last_error' => null,
        ]);
    }
}
