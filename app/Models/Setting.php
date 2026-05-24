<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    public const GROUP_GENERAL = 'general';
    public const GROUP_AI = 'ai';
    public const GROUP_SCRAPER = 'scraper';
    public const GROUP_FACEBOOK = 'facebook';

    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_JSON = 'json';

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            self::TYPE_INTEGER => (int) $setting->value,
            self::TYPE_BOOLEAN => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(string $key, mixed $value, string $type = self::TYPE_STRING, ?string $group = null, ?string $description = null): void
    {
        $stringValue = match (gettype($value)) {
            'boolean' => $value ? '1' : '0',
            'array', 'object' => json_encode($value),
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group ?? 'general',
                'value' => $stringValue,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function getGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('group', $group)->get();
    }

    public static function getAllAsArray(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function defaultSettings(): array
    {
        return [
            'general.app_name' => ['value' => 'Land Search AI', 'type' => self::TYPE_STRING, 'group' => self::GROUP_GENERAL, 'description' => 'Tên ứng dụng'],
            'general.timezone' => ['value' => 'Asia/Ho_Chi_Minh', 'type' => self::TYPE_STRING, 'group' => self::GROUP_GENERAL, 'description' => 'Múi giờ hệ thống'],
            'general.locale' => ['value' => 'vi', 'type' => self::TYPE_STRING, 'group' => self::GROUP_GENERAL, 'description' => 'Ngôn ngữ'],

            'ai.provider' => ['value' => 'gemini', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'Nhà cung cấp AI'],
            'ai.ollama_url' => ['value' => 'https://ollama.com', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'URL máy chủ Ollama (local, ví dụ http://localhost:11434). Có ai.ollama_api_key thì gọi ollama.com, trường này bị bỏ qua khi extract.'],
            'ai.ollama_model' => ['value' => 'gpt-oss:120b-cloud', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'Model Ollama'],
            'ai.ollama_api_key' => ['value' => '', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'API Key Ollama'],
            'ai.openai_api_key' => ['value' => '', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'API Key OpenAI'],
            'ai.gemini_api_key' => ['value' => '', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'API Key Gemini'],
            'ai.gemini_model' => ['value' => 'gemini-2.0-flash', 'type' => self::TYPE_STRING, 'group' => self::GROUP_AI, 'description' => 'Model Gemini'],

            'scraper.auto_enabled' => ['value' => '0', 'type' => self::TYPE_BOOLEAN, 'group' => self::GROUP_SCRAPER, 'description' => 'Bật tự động scrape'],
            'scraper.schedule_time' => ['value' => '06:00', 'type' => self::TYPE_STRING, 'group' => self::GROUP_SCRAPER, 'description' => 'Giờ scrape tự động'],
            'scraper.timeout' => ['value' => '300', 'type' => self::TYPE_INTEGER, 'group' => self::GROUP_SCRAPER, 'description' => 'Timeout scrape (giây)'],
            'scraper.concurrent_jobs' => ['value' => '3', 'type' => self::TYPE_INTEGER, 'group' => self::GROUP_SCRAPER, 'description' => 'Số job đồng thời'],
            'scraper.daily_limit' => ['value' => '100', 'type' => self::TYPE_INTEGER, 'group' => self::GROUP_SCRAPER, 'description' => 'Giới hạn bài/ngày'],
            'scraper.apify_token' => ['value' => '', 'type' => self::TYPE_STRING, 'group' => self::GROUP_SCRAPER, 'description' => 'Apify API Token'],
        ];
    }

    public static function initDefaults(): void
    {
        foreach (static::defaultSettings() as $key => $config) {
            static::firstOrCreate(
                ['key' => $key],
                [
                    'group' => $config['group'],
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                ]
            );
        }
    }
}
