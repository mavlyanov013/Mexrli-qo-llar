<?php

namespace App\Models\Concerns;

use App\Support\LocalizedContent;

trait HasLocalizedAttributes
{
    protected static function bootHasLocalizedAttributes(): void
    {
        static::saving(function ($model) {
            if (! property_exists($model, 'localizedAttributes')) {
                return;
            }

            foreach ($model->localizedAttributes as $attribute) {
                $localized = $model->getAttribute("{$attribute}_uz");

                if ($localized !== null && $localized !== '') {
                    $model->setAttribute($attribute, $localized);
                }
            }
        });
    }

    public function localized(string $field, ?string $locale = null): ?string
    {
        return LocalizedContent::resolve($this, $field, $locale);
    }
}
