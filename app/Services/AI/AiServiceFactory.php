<?php

namespace App\Services\AI;

use App\Models\Setting;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAIProvider;

class AiServiceFactory
{
    public static function make(?string $provider = null): AiProviderInterface
    {
        $provider = $provider ?? Setting::getValue('ai.provider', 'gemini');

        return match ($provider) {
            'ollama' => new OllamaProvider(),
            'openai' => new OpenAIProvider(),
            'gemini' => new GeminiProvider(),
            default => new GeminiProvider(),
        };
    }

    public static function extract(string $content, ?string $provider = null): array
    {
        $aiService = self::make($provider);
        return $aiService->extract($content);
    }

    public static function checkHealth(?string $provider = null, array $overrides = []): array
    {
        $provider = $provider ?? Setting::getValue('ai.provider', 'gemini');

        if ($provider === 'ollama') {
            return (new OllamaProvider($overrides))->checkHealth();
        }

        $aiService = self::make($provider);
        return $aiService->checkHealth();
    }

    public static function getAvailableProviders(): array
    {
        return [
            'ollama' => [
                'name' => 'Ollama',
                'description' => 'Local or self-hosted LLM',
                'model' => Setting::getValue('ai.ollama_model', 'llama3.2'),
                'configured' => ! empty(Setting::getValue('ai.ollama_api_key'))
                    || ! empty(Setting::getValue('ai.ollama_url')),
            ],
            'openai' => [
                'name' => 'OpenAI',
                'description' => 'GPT-4 and GPT-3.5 models',
                'model' => 'gpt-4o',
                'configured' => !empty(Setting::getValue('ai.openai_api_key')),
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'description' => 'Gemini 2.0 Flash and Pro',
                'model' => Setting::getValue('ai.gemini_model', 'gemini-2.0-flash'),
                'configured' => !empty(Setting::getValue('ai.gemini_api_key')),
            ],
        ];
    }

    public static function getDefaultProvider(): string
    {
        return Setting::getValue('ai.provider', 'gemini');
    }

    public static function getCurrentProvider(): string
    {
        return self::getDefaultProvider();
    }
}
