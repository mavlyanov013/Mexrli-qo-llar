<?php

namespace App\Support;

class CasePhotos
{
    /**
     * @param  array<int, array<string, mixed>|string>  $photos
     * @return array<int, array{url: string|null, name: string}>
     */
    public static function normalizeList(?array $photos): array
    {
        return collect($photos ?? [])
            ->map(function ($photo) {
                if (is_string($photo)) {
                    return [
                        'url' => MediaUrl::publicUrl($photo),
                        'name' => basename($photo),
                    ];
                }

                $rawUrl = $photo['url'] ?? null;

                return [
                    'url' => MediaUrl::publicUrl(is_string($rawUrl) ? $rawUrl : null),
                    'name' => (string) ($photo['name'] ?? basename((string) $rawUrl)),
                ];
            })
            ->filter(fn (array $photo) => ! empty($photo['url']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{url: string|null, name: string}>
     */
    public static function resolveForModel(?string $photoUrl, ?array $photos): array
    {
        $normalized = self::normalizeList($photos);

        if ($normalized !== []) {
            return $normalized;
        }

        $cover = MediaUrl::publicUrl($photoUrl);

        if (! $cover) {
            return [];
        }

        return [[
            'url' => $cover,
            'name' => 'Asosiy rasm',
        ]];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function syncValidated(array $validated): array
    {
        if (array_key_exists('photos', $validated)) {
            $validated['photos'] = self::normalizeList($validated['photos']);
            $validated['photo_url'] = $validated['photos'][0]['url'] ?? null;

            return $validated;
        }

        if (! empty($validated['photo_url'])) {
            $validated['photos'] = self::resolveForModel($validated['photo_url'], null);
        }

        return $validated;
    }
}
