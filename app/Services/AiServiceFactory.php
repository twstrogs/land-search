<?php

namespace App\Services;

use App\Contracts\AiProviderInterface;
use App\Models\Setting;
use App\Services\Providers\GeminiProvider;
use App\Services\Providers\OllamaProvider;

class AiServiceFactory
{
    private static ?AiProviderInterface $currentProvider = null;

    public static function make(?string $provider = null): AiProviderInterface
    {
        // Normalize provider name: ollama-local and ollama-cloud both use OllamaProvider
        $provider = $provider ?? self::getDefaultProvider();
        
        if (in_array($provider, ['ollama-local', 'ollama-cloud', 'ollama'])) {
            return new OllamaProvider($provider);
        }

        return match ($provider) {
            'gemini' => new GeminiProvider(),
            default => throw new \InvalidArgumentException("AI provider '{$provider}' not supported"),
        };
    }

    public static function extract(string $content, ?string $provider = null): array
    {
        $aiService = self::make($provider);
        return $aiService->extract($content);
    }

    public static function getAvailableProviders(): array
    {
        return [
            'ollama-local' => [
                'name' => 'Ollama (Local)',
                'description' => 'Chạy trên máy tính - Miễn phí',
                'models' => config('services.ollama.available_models', [
                    'llama3.2',
                    'llama3.1',
                    'mistral',
                    'gemma2',
                    'phi3',
                ]),
                'configured' => (bool) config('services.ollama.url'),
            ],
            'ollama-cloud' => [
                'name' => 'Ollama (Cloud)',
                'description' => 'Dùng AI trên server ollama.com - Cần API key',
                'models' => config('services.ollama.cloud_models', [
                    'gpt-oss:120b-cloud',
                    'llama3.2',
                ]),
                'configured' => !empty(config('services.ollama.api_key')),
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'description' => 'AI từ Google - Cần API key',
                'models' => config('services.gemini.available_models', [
                    'gemini-2.0-flash',
                    'gemini-1.5-flash',
                    'gemini-1.5-pro',
                ]),
                'configured' => (bool) config('services.gemini.api_key'),
            ],
        ];
    }

    /**
     * Get default AI provider from Setting model
     */
    public static function getDefaultProvider(): string
    {
        return Setting::getValue('ai.provider', 'gemini');
    }

    public static function getCurrentProvider(): string
    {
        return self::getDefaultProvider();
    }

    public static function setDefaultProvider(string $provider): void
    {
        Setting::setValue('ai.provider', $provider, Setting::TYPE_STRING, Setting::GROUP_AI);
    }

    public static function checkHealth(?string $provider = null): array
    {
        $aiService = self::make($provider);
        return $aiService->checkHealth();
    }
}
