<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    public function set(string $key, mixed $value, string $type = Setting::TYPE_STRING): void
    {
        Setting::setValue($key, $value, $type);
    }

    public function getByGroup(string $group): Collection
    {
        return Setting::where('group', $group)->get();
    }

    public function getGroupAsArray(string $group): array
    {
        return Setting::getAllAsArray($group);
    }

    public function saveGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            $fullKey = "{$group}.{$key}";
            $setting = Setting::where('key', $fullKey)->first();
            
            if ($setting) {
                $setting->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
            }
        }
    }

    public function initDefaults(): void
    {
        Setting::initDefaults();
    }
}
