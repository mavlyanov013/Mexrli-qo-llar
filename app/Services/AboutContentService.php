<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Support\LocalizedContent;
use Illuminate\Support\Facades\Storage;

class AboutContentService
{
    public const BANK_KEY = 'about_bank_details';

    public const LEGAL_KEY = 'about_legal_info';

    public const DOCS_KEY = 'about_documents';

    public const DOC_REGISTRATION = 'registration_certificate';

    public const DOC_CHARTER = 'organization_charter';

    public const DOCUMENT_KEYS = [
        self::DOC_REGISTRATION,
        self::DOC_CHARTER,
    ];

    private const LOCALES = ['uz', 'oz', 'ru'];

    private const DOCUMENT_DEFAULTS = [
        self::DOC_REGISTRATION => [
            'title' => 'Davlat ro‘yxatidan o‘tganlik guvohnomasi',
            'description' => 'Tashkilot ro‘yxatdan o‘tganini tasdiqlovchi rasmiy hujjat',
        ],
        self::DOC_CHARTER => [
            'title' => 'Tashkilot nizomi',
            'description' => 'Jamg‘arma faoliyatini tartibga soluvchi asosiy hujjat',
        ],
    ];

    public function getContent(): array
    {
        return [
            'bank' => $this->getBankDetails(),
            'legal' => $this->getLegalInfo(),
            'docs' => $this->getDocuments(),
            'team' => TeamMember::query()->orderBy('sort_order')->orderBy('id')->get(),
        ];
    }

    public function getDocuments(): array
    {
        $stored = $this->getSettingJson(self::DOCS_KEY, []);
        $legacy = $this->legacyDocuments();

        return collect(self::DOCUMENT_KEYS)
            ->map(function (string $key) use ($stored, $legacy) {
                $defaults = self::DOCUMENT_DEFAULTS[$key];
                $legacyItem = $legacy[$key] ?? [];
                $item = $stored[$key] ?? [];

                if (! isset($item['uz']) && isset($item['title'])) {
                    $item = [
                        'uz' => [
                            'title' => $item['title'] ?? $legacyItem['title'] ?? $defaults['title'],
                            'description' => $item['description'] ?? $legacyItem['description'] ?? $defaults['description'],
                        ],
                        'file' => $item['file'] ?? $legacyItem['file'] ?? '',
                    ];
                }

                $block = $this->normalizeLocaleBlock(
                    $item,
                    [
                        'title' => $defaults['title'],
                        'description' => $defaults['description'],
                    ]
                );

                $file = $item['file'] ?? $legacyItem['file'] ?? '';

                return array_merge(
                    $this->flattenLocalizedFields($block, ['title', 'description']),
                    [
                        'key' => $key,
                        'file' => $file,
                        'file_url' => $this->resolveFileUrl($file),
                    ]
                );
            })
            ->values()
            ->all();
    }

    public function saveDocument(string $key, array $data): array
    {
        if (! in_array($key, self::DOCUMENT_KEYS, true)) {
            abort(404, 'Document not found');
        }

        $stored = $this->getSettingJson(self::DOCS_KEY, []);
        $current = $stored[$key] ?? [];
        $block = $this->buildLocaleBlockFromInput($data, ['title', 'description'], $current);
        $file = $data['file'] ?? ($current['file'] ?? '');

        $stored[$key] = array_merge($block, ['file' => $file]);
        $this->saveSettingJson(self::DOCS_KEY, $stored);

        return collect($this->getDocuments())->firstWhere('key', $key) ?? [];
    }

    public function getBankDetails(): array
    {
        $defaults = [
            'bank' => '',
            'account_uzs' => '',
            'mfo_bik' => '',
        ];

        $stored = $this->getSettingJson(self::BANK_KEY, []);

        if (empty(array_filter($stored))) {
            $legacy = $this->legacyLegalMap();
            $stored = [
                'bank' => $legacy['bank'] ?? '',
                'account_uzs' => $legacy['accountUzs'] ?? '',
                'mfo_bik' => $legacy['mfoBik'] ?? '',
            ];
        }

        $block = $this->normalizeLocaleBlock($stored, $defaults);

        return array_merge(
            $this->flattenLocalizedFields($block, ['bank']),
            [
                'account_uzs' => $block['uz']['account_uzs'] ?? '',
                'mfo_bik' => $block['uz']['mfo_bik'] ?? '',
            ]
        );
    }

    public function getLegalInfo(): array
    {
        $defaults = [
            'org_name' => '',
            'inn' => '',
            'legal_address' => '',
        ];

        $stored = $this->getSettingJson(self::LEGAL_KEY, []);

        if (empty(array_filter($stored))) {
            $legacy = $this->legacyLegalMap();
            $stored = [
                'org_name' => $legacy['orgName'] ?? '',
                'inn' => $legacy['inn'] ?? '',
                'legal_address' => $legacy['legalAddress'] ?? '',
            ];
        }

        $block = $this->normalizeLocaleBlock($stored, $defaults);

        return array_merge(
            $this->flattenLocalizedFields($block, ['org_name', 'legal_address']),
            [
                'inn' => $block['uz']['inn'] ?? '',
            ]
        );
    }

    public function saveBankDetails(array $data): array
    {
        $block = $this->buildLocaleBlockFromInput($data, ['bank'], $this->getSettingJson(self::BANK_KEY, []));

        foreach (self::LOCALES as $locale) {
            $block[$locale]['account_uzs'] = $data['account_uzs'] ?? $block[$locale]['account_uzs'] ?? '';
            $block[$locale]['mfo_bik'] = $data['mfo_bik'] ?? $block[$locale]['mfo_bik'] ?? '';
        }

        $this->saveSettingJson(self::BANK_KEY, $block);

        return $this->getBankDetails();
    }

    public function saveLegalInfo(array $data): array
    {
        $block = $this->buildLocaleBlockFromInput($data, ['org_name', 'legal_address'], $this->getSettingJson(self::LEGAL_KEY, []));

        foreach (self::LOCALES as $locale) {
            $block[$locale]['inn'] = $data['inn'] ?? $block[$locale]['inn'] ?? '';
        }

        $this->saveSettingJson(self::LEGAL_KEY, $block);

        return $this->getLegalInfo();
    }

    private function normalizeLocaleBlock(array $stored, array $defaultUz): array
    {
        if (isset($stored['uz']) || isset($stored['oz']) || isset($stored['ru'])) {
            $block = [];
            foreach (self::LOCALES as $locale) {
                $block[$locale] = array_merge($defaultUz, $stored[$locale] ?? []);
            }

            return $block;
        }

        $legacy = array_merge($defaultUz, $stored);

        return [
            'uz' => $legacy,
            'oz' => $defaultUz,
            'ru' => $defaultUz,
        ];
    }

    private function buildLocaleBlockFromInput(array $data, array $fields, array $current = []): array
    {
        $currentBlock = $this->normalizeLocaleBlock($current, array_fill_keys($fields, ''));
        $block = [];

        foreach (self::LOCALES as $locale) {
            $entry = $currentBlock[$locale] ?? [];
            foreach ($fields as $field) {
                $entry[$field] = $data["{$field}_{$locale}"] ?? $entry[$field] ?? '';
            }
            $block[$locale] = $entry;
        }

        return $block;
    }

    private function flattenLocalizedFields(array $block, array $fields): array
    {
        $flat = [];

        foreach ($fields as $field) {
            foreach (self::LOCALES as $locale) {
                $flat["{$field}_{$locale}"] = $block[$locale][$field] ?? '';
            }

            $flat[$field] = $this->resolveFromBlock($block, $field);
        }

        return $flat;
    }

    private function resolveFromBlock(array $block, string $field, ?string $locale = null): string
    {
        $suffix = LocalizedContent::localeToSuffix($locale ?? LocalizedContent::resolveLocale());
        $value = $block[$suffix][$field] ?? '';

        if ($suffix === 'oz' && $value === '') {
            $value = $block['uz'][$field] ?? '';
        }

        if ($value === '' && $suffix === 'ru') {
            $value = $block['uz'][$field] ?? '';
        }

        return $value;
    }

    private function getSettingJson(string $key, array $default = []): array
    {
        $setting = Setting::query()->where('key', $key)->first();

        if (! $setting?->value) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    private function saveSettingJson(string $key, array $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]
        );
    }

    private function legacyLegalMap(): array
    {
        $map = [];

        Section::query()
            ->where('type', 'legal')
            ->get()
            ->each(function (Section $section) use (&$map) {
                if ($section->title) {
                    $map[$section->title] = $section->content;
                }
            });

        return $map;
    }

    private function legacyDocuments(): array
    {
        $pageId = \App\Models\Page::query()->where('slug', 'about')->value('id');

        if (! $pageId) {
            return [];
        }

        $sections = Section::query()
            ->where('page_id', $pageId)
            ->where('type', 'doc')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $keys = self::DOCUMENT_KEYS;
        $mapped = [];

        foreach ($sections as $index => $section) {
            $key = $keys[$index] ?? null;

            if (! $key) {
                break;
            }

            $mapped[$key] = [
                'title' => $section->title ?? self::DOCUMENT_DEFAULTS[$key]['title'],
                'description' => $section->content ?? self::DOCUMENT_DEFAULTS[$key]['description'],
                'file' => $section->file_path ?? '',
            ];
        }

        return $mapped;
    }

    private function resolveFileUrl(?string $file): ?string
    {
        if (! $file) {
            return null;
        }

        if (str_starts_with($file, 'http') || str_starts_with($file, '/')) {
            return $file;
        }

        return Storage::disk('public')->url($file);
    }
}
