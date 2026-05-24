<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function __construct(
        protected SettingRepository $settingRepository
    ) {}

    public function index(): View
    {
        $aiSettings = $this->settingRepository->getByGroup('ai')->pluck('value', 'key')->mapWithKeys(function ($value, $key) {
            return [last(explode('.', $key)) => $value];
        })->toArray();
        
        if (!isset($aiSettings['ollama_model'])) {
            $aiSettings['ollama_model'] = 'gpt-oss:120b-cloud';
        }
        
        if (! isset($aiSettings['ollama_url'])) {
            $aiSettings['ollama_url'] = 'https://ollama.com';
        }
        
        $scraperSettings = $this->settingRepository->getByGroup('scraper')->pluck('value', 'key')->mapWithKeys(function ($value, $key) {
            return [last(explode('.', $key)) => $value];
        })->toArray();
        
        $generalSettings = $this->settingRepository->getByGroup('general')->pluck('value', 'key')->mapWithKeys(function ($value, $key) {
            return [last(explode('.', $key)) => $value];
        })->toArray();

        return view('dashboard.settings.index', [
            'aiSettings' => $aiSettings,
            'scraperSettings' => $scraperSettings,
            'generalSettings' => $generalSettings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                $type = $setting->type;
                $finalValue = match ($type) {
                    'integer' => (int) $value,
                    'boolean' => $value ? true : false,
                    'json' => is_array($value) ? $value : json_decode($value, true),
                    default => $value,
                };
                
                $setting->update(['value' => is_array($finalValue) ? json_encode($finalValue) : (string) $finalValue]);
            }
        }

        return redirect()
            ->route('dashboard.settings.index')
            ->with('success', 'Cài đặt đã được lưu thành công.');
    }

    public function testAiProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:ollama,gemini,openai',
            'ollama_model' => 'nullable|string',
            'ollama_url' => 'nullable|string',
            'ollama_api_key' => 'nullable|string',
        ]);

        $overrides = [];
        if ($validated['provider'] === 'ollama') {
            $overrides = [
                'model' => $validated['ollama_model'] ?? null,
                'url' => $validated['ollama_url'] ?? null,
                'api_key' => $validated['ollama_api_key'] ?? null,
            ];
        }

        $health = \App\Services\AI\AiServiceFactory::checkHealth($validated['provider'], $overrides);

        $ok = ($health['status'] ?? '') === 'healthy';

        return response()->json([
            'success' => $ok,
            'message' => $ok
                ? ($health['message'] ?? 'Kết nối thành công')
                : null,
            'error' => $ok ? null : ($health['error'] ?? 'Kiểm tra thất bại'),
            'details' => $health,
        ]);
    }
}
