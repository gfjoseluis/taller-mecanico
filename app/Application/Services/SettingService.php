<?php

namespace App\Application\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? config($key, $default);
        });
    }

    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => is_scalar($value) ? (string) $value : json_encode($value)]);
        Cache::forget("setting.{$key}");
    }

    public function allGrouped(): array
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    public static function flush(string $key): void
    {
        Cache::forget("setting.{$key}");
    }
}
