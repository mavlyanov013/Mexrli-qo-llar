<?php

namespace App\Support;

class MediaUrl
{
    public static function publicUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null || $pathOrUrl === '') {
            return null;
        }

        $value = trim($pathOrUrl);

        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';

            if (str_starts_with($path, '/storage/')) {
                return $path;
            }

            return $value;
        }

        return '/storage/' . ltrim($value, '/');
    }
}
