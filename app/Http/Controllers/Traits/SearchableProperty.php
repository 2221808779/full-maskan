<?php

namespace App\Http\Controllers\Traits;

trait SearchableProperty
{
    /**
     * Match a search term against known property type keywords.
     */
    public function matchPropertyType(string $search): ?string
    {
        $typeKeywords = [
            'villa'      => ['villa', 'فيلا', 'فله', 'ڤيلا'],
            'apartment'  => ['apartment', 'apt', 'شقة', 'شق', 'شقه', 'شقق'],
            'resort'     => ['resort', 'منتجع', 'منتجعات', 'ريزورت'],
            'rest_house' => ['rest house', 'rest_house', 'استراحة', 'استراحات', 'مزرعة', 'farm'],
            'house'      => ['house', 'بيت', 'منزل', 'دور'],
            'building'   => ['building', 'عمارة', 'بناية', 'مبنى', 'برج'],
        ];

        $searchLower = mb_strtolower(trim($search));

        foreach ($typeKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if ($searchLower === mb_strtolower($keyword)) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Match a search term against known Libyan city names.
     */
    public function matchCity(string $search): ?string
    {
        $cities = config('cities.cities', []);
        $searchLower = mb_strtolower(trim($search));

        foreach ($cities as $city) {
            if (mb_strtolower($city) === $searchLower) {
                return $city;
            }
        }

        return null;
    }
}
