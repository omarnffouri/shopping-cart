<?php

namespace App\Traits;

trait Translatable
{

    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable')
            ? $this->translatable
            : [];
    }

    public function getTranslation(string $key, string $locale = null): string
    {
        $locale = $locale ?? $this->getLang();

        $translations = $this->getTranslations($key);

        // If empty translations array
        if (empty($translations)) {
            return '';
        }

        // Return translation for requested locale (if not empty)
        if (isset($translations[$locale]) && $translations[$locale] !== '' && $translations[$locale] !== null) {
            return $translations[$locale];
        }

        // Fallback to default locale (if not empty)
        $fallbackLocale = config('app.fallback_locale', 'en');
        if (isset($translations[$fallbackLocale]) && $translations[$fallbackLocale] !== '' && $translations[$fallbackLocale] !== null) {
            return $translations[$fallbackLocale];
        }

        // Return first non-empty translation
        foreach ($translations as $translation) {
            if ($translation !== '' && $translation !== null) {
                return $translation;
            }
        }

        // If all translations are empty, return empty string
        return '';
    }


    public function getTranslations(string $key): array
    {
        $value = $this->attributes[$key] ?? null;

        if (is_null($value)) {
            return [];
        }

        // If it's already an array (from cache), return it
        if (is_array($value)) {
            return $value;
        }

        // Decode JSON
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function setTranslation(string $key, string $locale, string $value): self
    {
        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        $this->attributes[$key] = json_encode($translations);

        return $this;
    }


    public function setTranslations(string $key, array $translations): self
    {
        $this->attributes[$key] = json_encode($translations);

        return $this;
    }


    public function getAttribute($key)
    {
        if (in_array($key, $this->getTranslatableAttributes())) {
            return $this->getTranslation($key);
        }

        return parent::getAttribute($key);
    }


    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getTranslatableAttributes())) {
            return $this->setTranslation($key, $this->getLang(), $value);
        }

        return parent::setAttribute($key, $value);
    }


    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $attribute) {
            if (isset($array[$attribute])) {
                // Convert JSON to current locale value
                $array[$attribute] = $this->getTranslation($attribute);
            }
        }

        return $array;
    }

    public function scopeWhereTranslationLike($query, string $attribute, string $locale, string $value)
    {
        return $query->whereRaw(
            "JSON_EXTRACT({$attribute}, '$.{$locale}') LIKE ?",
            ["%{$value}%"]
        );
    }


    private function getLang(): array|string|null
    {
        return request()->header('X-lang') ?? app()->getLocale();
    }
}
