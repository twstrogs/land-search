<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromptVersion extends Model
{
    protected $fillable = [
        'name',
        'version',
        'system_prompt',
        'few_shot_examples',
        'output_schema',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'few_shot_examples' => 'array',
        'output_schema' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get extraction logs for this prompt version
     */
    public function extractionLogs(): HasMany
    {
        return $this->hasMany(ExtractionLog::class, 'prompt_version_id');
    }

    /**
     * Scope to active prompts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to specific task type
     */
    public function scopeForTask($query, string $taskType)
    {
        return $query->where('name', $taskType);
    }

    /**
     * Activate this prompt version
     */
    public function activate(): void
    {
        // Deactivate others
        self::where('name', $this->name)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this one
        $this->update(['is_active' => true]);
    }

    /**
     * Get latest version number
     */
    public static function getLatestVersion(string $taskType): ?string
    {
        $latest = self::where('name', $taskType)
            ->orderBy('version', 'desc')
            ->first();

        return $latest?->version;
    }

    /**
     * Increment version
     */
    public function getNextVersion(): string
    {
        $latest = self::getLatestVersion($this->name);
        
        if (!$latest) {
            return 'v1.0';
        }

        // Parse version number
        if (preg_match('/^v(\d+)\.(\d+)$/', $latest, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];
            return "v{$major}." . ($minor + 1);
        }

        return $latest . '.1';
    }
}
