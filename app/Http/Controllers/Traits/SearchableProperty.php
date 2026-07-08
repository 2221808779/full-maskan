<?php

namespace App\Http\Controllers\Traits;

trait SearchableProperty
{
    /**
     * مطابقة نوع العقار — يبحث عن كلمة مفتاحية تطابق نوع العقار (دعم العربية والإنجليزية)
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
                $keywordLower = mb_strtolower($keyword);
                if ($searchLower === $keywordLower || str_contains($searchLower, $keywordLower)) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * مطابقة المدينة — يبحث عن اسم مدينة ليبية ضمن نص البحث
     */
    public function matchCity(string $search): ?string
    {
        $cities = config('cities.cities', []);
        $searchLower = mb_strtolower(trim($search));

        foreach ($cities as $city) {
            $cityLower = mb_strtolower($city);
            if ($searchLower === $cityLower || str_contains($searchLower, $cityLower)) {
                return $city;
            }
        }

        return null;
    }
}
