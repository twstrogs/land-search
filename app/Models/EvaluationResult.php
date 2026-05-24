<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResult extends Model
{
    protected $fillable = [
        'test_dataset_name',
        'method',
        'provider',
        'model',
        'accuracy',
        'precision_score',
        'recall_score',
        'f1_score',
        'field_metrics',
        'total_samples',
        'processing_time_ms_avg',
        'cost_per_1k_tokens',
        'error_count',
        'config',
        'metadata',
    ];

    protected $casts = [
        'accuracy' => 'decimal:4',
        'precision_score' => 'decimal:4',
        'recall_score' => 'decimal:4',
        'f1_score' => 'decimal:4',
        'field_metrics' => 'array',
        'config' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get dataset
     */
    public function dataset()
    {
        return $this->belongsTo(EvaluationDataset::class, 'test_dataset_name', 'name');
    }

    /**
     * Scope to method
     */
    public function scopeForMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope to provider
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get formatted accuracy
     */
    public function getAccuracyPercentAttribute(): string
    {
        return round($this->accuracy * 100, 2) . '%';
    }

    /**
     * Get formatted F1
     */
    public function getF1PercentAttribute(): string
    {
        return round($this->f1_score * 100, 2) . '%';
    }

    /**
     * Get best result for dataset
     */
    public static function getBestForDataset(string $datasetName, string $metric = 'f1_score'): ?self
    {
        return self::where('test_dataset_name', $datasetName)
            ->orderBy($metric, 'desc')
            ->first();
    }

    /**
     * Get comparison between methods
     */
    public static function compareMethods(string $datasetName): array
    {
        $results = self::where('test_dataset_name', $datasetName)
            ->orderBy('method')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('method');

        return $results->mapWithKeys(function ($result) {
            return [$result->method => $result];
        })->toArray();
    }
}
