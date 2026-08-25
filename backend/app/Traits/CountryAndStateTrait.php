<?php

namespace App\Traits;

trait CountryAndStateTrait {

    const COUNTRY_CODE = "AE";

    function getCountries(): void
    {
        $this->countries()->values();
    }

    function getCountryByCountryCode($country = self::COUNTRY_CODE)
    {
        return $this->countries()
            ->when($country, function ($query, $country) {
                return $query->where('code2', $country);
            })->values()
            ?->first();
    }

    private function countries (): \Illuminate\Support\Collection
    {
        return collect(json_decode(file_get_contents(base_path() . '/storage/resources/countries.json') , true));
    }
}
