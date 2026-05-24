<?php

namespace App\Search;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Embedding Service
 * 
 * Handles text embedding generation for semantic search:
 * - Uses OpenAI or Ollama embeddings
 * - Caches embeddings
 * - Supports batch processing
 */
class EmbeddingService
{
    private string $provider;
    private string $model;
    private int $embeddingDimension;
    private array $config;

    public function __construct()
    {
        $this->provider = config('services.embedding.provider', 'openai');
        $this->model = config('services.embedding.model', 'text-embedding-3-small');
        $this->embeddingDimension = config('services.embedding.dimension', 1536);
        $this->config = config('services.embedding', []);
    }

    /**
     * Generate embedding for a single text
     */
    public function embed(string $text): array
    {
        // Check cache
        $cacheKey = 'embedding_' . md5($text);
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        $embedding = match ($this->provider) {
            'openai' => $this->getOpenAIEmbedding($text),
            'ollama' => $this->getOllamaEmbedding($text),
            'local' => $this->getLocalEmbedding($text),
            default => throw new \Exception("Unknown embedding provider: {$this->provider}"),
        };

        // Cache embedding
        Cache::put($cacheKey, $embedding, now()->addDays(30));

        return $embedding;
    }

    /**
     * Generate embeddings for multiple texts (batch)
     */
    public function embedBatch(array $texts, int $batchSize = 100): array
    {
        $embeddings = [];

        foreach (array_chunk($texts, $batchSize) as $batch) {
            $batchEmbeddings = match ($this->provider) {
                'openai' => $this->getOpenAIBatchEmbeddings($batch),
                'ollama' => $this->getOllamaBatchEmbeddings($batch),
                default => throw new \Exception("Batch not supported for provider: {$this->provider}"),
            };

            $embeddings = array_merge($embeddings, $batchEmbeddings);
        }

        return $embeddings;
    }

    /**
     * Get OpenAI embedding
     */
    private function getOpenAIEmbedding(string $text): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/embeddings', [
                'input' => $text,
                'model' => $this->model,
                'dimensions' => $this->embeddingDimension,
            ]);

        if (!$response->successful()) {
            Log::error('[EmbeddingService] OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('OpenAI embedding failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['data'][0]['embedding'];
    }

    /**
     * Get OpenAI batch embeddings
     */
    private function getOpenAIBatchEmbeddings(array $texts): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/embeddings', [
                'input' => $texts,
                'model' => $this->model,
                'dimensions' => $this->embeddingDimension,
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI batch embedding failed');
        }

        $data = $response->json();
        
        // Sort by index to maintain order
        $embeddings = [];
        foreach ($data['data'] as $item) {
            $embeddings[$item['index']] = $item['embedding'];
        }

        return $embeddings;
    }

    /**
     * Get Ollama embedding
     */
    private function getOllamaEmbedding(string $text): array
    {
        $baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $model = config('services.ollama.embedding_model', 'nomic-embed-text');

        $response = Http::timeout(30)
            ->post("{$baseUrl}/api/embed", [
                'model' => $model,
                'input' => $text,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama embedding failed');
        }

        $data = $response->json();
        return $data['embeddings'][0] ?? [];
    }

    /**
     * Get Ollama batch embeddings
     */
    private function getOllamaBatchEmbeddings(array $texts): array
    {
        $baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $model = config('services.ollama.embedding_model', 'nomic-embed-text');

        $response = Http::timeout(60)
            ->post("{$baseUrl}/api/embed", [
                'model' => $model,
                'input' => $texts,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama batch embedding failed');
        }

        $data = $response->json();
        return $data['embeddings'] ?? [];
    }

    /**
     * Get local/simple embedding (fallback)
     */
    private function getLocalEmbedding(string $text): array
    {
        // Simple hash-based embedding as fallback
        // In production, use proper embedding model
        $hash = md5($text);
        $embedding = [];
        
        for ($i = 0; $i < $this->embeddingDimension; $i++) {
            $embedding[] = hexdec(substr($hash, $i * 2, 2)) / 255.0;
        }

        return $embedding;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Build searchable content from post
     */
    public function buildSearchableContent(Post $post): string
    {
        $parts = [];

        // Title
        if ($post->title) {
            $parts[] = "Tiêu đề: {$post->title}";
        }

        // Description
        if ($post->description) {
            $parts[] = "Mô tả: {$post->description}";
        }

        // Address
        if ($post->address_text) {
            $parts[] = "Địa chiểm: {$post->address_text}";
        }

        // Features
        $features = $post->features->pluck('name')->toArray();
        if (!empty($features)) {
            $parts[] = "Đặc điểm: " . implode(', ', $features);
        }

        // Price
        if ($post->price_text) {
            $parts[] = "Giá: {$post->price_text}";
        }

        // Area
        if ($post->area_text) {
            $parts[] = "Diện tích: {$post->area_text}";
        }

        return implode('. ', $parts);
    }

    /**
     * Get embedding dimension
     */
    public function getDimension(): int
    {
        return $this->embeddingDimension;
    }

    /**
     * Get provider info
     */
    public function getProviderInfo(): array
    {
        return [
            'provider' => $this->provider,
            'model' => $this->model,
            'dimension' => $this->embeddingDimension,
        ];
    }
}
