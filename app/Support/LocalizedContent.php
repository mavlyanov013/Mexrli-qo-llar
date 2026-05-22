<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LocalizedContent
{
    public const SUFFIXES = ['uz', 'oz', 'ru'];

    public static function localeToSuffix(?string $locale): string
    {
        return match ($locale) {
            'ru' => 'ru',
            'uz_cyrl', 'oz', 'cyrl' => 'oz',
            default => 'uz',
        };
    }

    public static function resolveLocale(?Request $request = null): string
    {
        $request ??= request();

        if (! $request) {
            return 'uz';
        }

        return (string) ($request->header('X-Locale')
            ?? $request->query('locale')
            ?? 'uz');
    }

    public static function resolve(Model|array $source, string $field, ?string $locale = null): ?string
    {
        $locale ??= self::resolveLocale();
        $suffix = self::localeToSuffix($locale);
        $value = self::readValue($source, "{$field}_{$suffix}");

        if ($suffix === 'oz' && self::isEmpty($value)) {
            $value = self::readValue($source, "{$field}_uz");
        }

        if (self::isEmpty($value)) {
            $value = self::readValue($source, $field);
        }

        return self::isEmpty($value) ? null : (string) $value;
    }

    public static function append(Model $model, string $field, array $payload = []): array
    {
        foreach (self::SUFFIXES as $suffix) {
            $payload["{$field}_{$suffix}"] = $model->{"{$field}_{$suffix}"};
        }

        $payload[$field] = self::resolve($model, $field);

        return $payload;
    }

    public static function validationRules(string $field, bool $required = true, ?int $max = null): array
    {
        $stringRule = $required ? 'required' : 'nullable';
        $maxRule = $max ? "max:{$max}" : null;

        $rules = [];

        foreach (self::SUFFIXES as $suffix) {
            $suffixRules = array_filter([$stringRule, 'string', $maxRule]);
            $rules["{$field}_{$suffix}"] = $suffixRules;
        }

        return $rules;
    }

    /** Admin: lotin (_uz) majburiy; kirill (_oz) va rus (_ru) ixtiyoriy */
    public static function adminValidationRules(string $field, bool $required = true, ?int $max = null): array
    {
        $requiredRule = $required ? 'required' : 'nullable';
        $maxRule = $max ? "max:{$max}" : null;

        return [
            "{$field}_uz" => array_filter([$requiredRule, 'string', $maxRule]),
            "{$field}_oz" => array_filter(['nullable', 'string', $maxRule]),
            "{$field}_ru" => array_filter(['nullable', 'string', $maxRule]),
        ];
    }

    public static function prepareAdminLocalized(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            $uz = trim((string) ($data["{$field}_uz"] ?? $data[$field] ?? ''));

            if ($uz !== '') {
                $data["{$field}_uz"] = $uz;
                $data[$field] = $uz;
            }

            $oz = trim((string) ($data["{$field}_oz"] ?? ''));
            $data["{$field}_oz"] = $oz === '' ? null : $oz;

            $ru = trim((string) ($data["{$field}_ru"] ?? ''));
            $data["{$field}_ru"] = $ru === '' ? null : $ru;
        }

        return $data;
    }

    public static function syncLegacyColumns(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (! empty($data["{$field}_uz"])) {
                $data[$field] = $data["{$field}_uz"];
            }
        }

        return $data;
    }

    public static function appendMany(Model $model, array $fields, array $payload = []): array
    {
        foreach ($fields as $field) {
            $payload = self::append($model, $field, $payload);
        }

        return $payload;
    }

    private static function readValue(Model|array $source, string $key): mixed
    {
        if ($source instanceof Model) {
            return $source->getAttribute($key);
        }

        return $source[$key] ?? null;
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}
