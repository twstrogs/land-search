<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationDataset extends Model
{
    protected $fillable = [
        'name',
        'description',
        'ground_truth',
        'test_samples',
        'total_samples',
        'source',
        'metadata',
    ];

    protected $casts = [
        'ground_truth' => 'array',
        'test_samples' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get evaluation results for this dataset
     */
    public function results(): HasMany
    {
        return $this->hasMany(EvaluationResult::class, 'test_dataset_name', 'name');
    }

    /**
     * Get test samples
     */
    public function getTestSamples(): array
    {
        return $this->test_samples ?? [];
    }

    /**
     * Get ground truth
     */
    public function getGroundTruth(): array
    {
        return $this->ground_truth ?? [];
    }

    /**
     * Get total sample count
     */
    public function getTotalSamples(): int
    {
        return $this->total_samples ?? count($this->test_samples ?? []);
    }

    /**
     * Add a test sample
     */
    public function addSample(string $input, array $groundTruth): void
    {
        $samples = $this->test_samples ?? [];
        $id = count($samples) + 1;
        
        $samples[] = [
            'id' => $id,
            'input' => $input,
        ];

        $truth = $this->ground_truth ?? [];
        $truth[$id] = $groundTruth;

        $this->update([
            'test_samples' => $samples,
            'ground_truth' => $truth,
            'total_samples' => count($samples),
        ]);
    }

    /**
     * Load dataset from file
     */
    public static function loadFromFile(string $path): self
    {
        $data = json_decode(file_get_contents($path), true);

        return self::create([
            'name' => $data['name'] ?? basename($path),
            'description' => $data['description'] ?? '',
            'ground_truth' => $data['ground_truth'] ?? [],
            'test_samples' => $data['test_samples'] ?? [],
            'total_samples' => count($data['test_samples'] ?? []),
            'source' => $path,
        ]);
    }

    /**
     * Export to JSON
     */
    public function exportToJson(): string
    {
        return json_encode([
            'name' => $this->name,
            'description' => $this->description,
            'ground_truth' => $this->ground_truth,
            'test_samples' => $this->test_samples,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
