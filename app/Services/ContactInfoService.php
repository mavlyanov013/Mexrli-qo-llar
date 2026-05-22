<?php

namespace App\Services;

use App\Models\Setting;

class ContactInfoService
{
    public const KEY = 'contact_info';

    public const SOCIAL_KEYS = [
        'instagram_url',
        'youtube_url',
        'facebook_url',
        'telegram_url',
    ];

    public function get(): array
    {
        $data = array_merge($this->defaults(), $this->getStored());

        return array_merge([
            'address' => (string) ($data['address'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'map_embed_url' => (string) ($data['map_embed_url'] ?? ''),
            'map_lat' => $data['map_lat'] ?? null,
            'map_lng' => $data['map_lng'] ?? null,
            'map_url' => $this->resolveMapUrl($data),
        ], $this->getSocialLinks());
    }

    public function update(array $payload): array
    {
        $current = array_merge($this->defaults(), $this->getStored());

        $next = [
            'address' => trim((string) ($payload['address'] ?? $current['address'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? $current['phone'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? $current['email'] ?? '')),
            'map_embed_url' => trim((string) ($payload['map_embed_url'] ?? $current['map_embed_url'] ?? '')),
            'map_lat' => $this->nullableFloat($payload['map_lat'] ?? $current['map_lat'] ?? null),
            'map_lng' => $this->nullableFloat($payload['map_lng'] ?? $current['map_lng'] ?? null),
        ];

        Setting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['value' => json_encode($next, JSON_UNESCAPED_UNICODE)]
        );

        foreach (self::SOCIAL_KEYS as $key) {
            if (array_key_exists($key, $payload)) {
                $this->saveSettingValue($key, $payload[$key]);
            }
        }

        return $this->get();
    }

    private function getSocialLinks(): array
    {
        $links = [];

        foreach (self::SOCIAL_KEYS as $key) {
            $links[$key] = $this->getSettingValue($key);
        }

        return $links;
    }

    private function getSettingValue(string $key): string
    {
        return trim((string) (Setting::query()->where('key', $key)->value('value') ?? ''));
    }

    private function saveSettingValue(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => trim((string) ($value ?? ''))]
        );
    }

    private function defaults(): array
    {
        return [
            'address' => '123 Charity Lane, Tashkent, Uzbekistan',
            'phone' => '+998711234567',
            'email' => 'info@mehrli.uz',
            'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=69.15%2C41.26%2C69.35%2C41.36&layer=mapnik',
            'map_lat' => 41.3111,
            'map_lng' => 69.2797,
        ];
    }

    private function getStored(): array
    {
        $setting = Setting::query()->where('key', self::KEY)->first();

        if (! $setting?->value) {
            return [];
        }

        $decoded = json_decode($setting->value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveMapUrl(array $data): string
    {
        $embed = trim((string) ($data['map_embed_url'] ?? ''));

        if ($embed !== '') {
            if (preg_match('/src=["\']([^"\']+)["\']/i', $embed, $matches)) {
                return $matches[1];
            }

            return $embed;
        }

        $lat = $this->nullableFloat($data['map_lat'] ?? null);
        $lng = $this->nullableFloat($data['map_lng'] ?? null);

        if ($lat !== null && $lng !== null) {
            $delta = 0.05;

            return sprintf(
                'https://www.openstreetmap.org/export/embed.html?bbox=%s%%2C%s%%2C%s%%2C%s&layer=mapnik&marker=%s%%2C%s',
                $lng - $delta,
                $lat - $delta,
                $lng + $delta,
                $lat + $delta,
                $lat,
                $lng
            );
        }

        return (string) ($this->defaults()['map_embed_url'] ?? '');
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
