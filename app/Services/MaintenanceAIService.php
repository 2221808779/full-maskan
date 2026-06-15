<?php

namespace App\Services;

use App\Models\MaintenancePrediction;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * خدمة الصيانة بالذكاء الاصطناعي — اتصال بـ FastAPI لتصنيف وتوقع طلبات الصيانة
 */
class MaintenanceAIService
{
    protected string $url;

    protected array $categoryIds = [
        'electricity' => 1,
        'plumbing' => 2,
        'air_conditioning' => 3,
        'painting' => 4,
        'carpentry' => 5,
        'other' => 6,
    ];

    protected array $keywords = [
        'electricity' => [
            'كهرباء', 'لمبة', 'فيش', 'مفتاح', 'قاطع', 'أسلاك', 'تماس', 'انقطاع', 'تيار', 'فيوز', 'لمبات', 'كهربا',
            'electricity', 'electrical', 'power', 'wire', 'cable', 'fuse', 'switch', 'socket', 'breaker', 'short circuit', 'outage', 'light',
        ],
        'plumbing' => [
            'سباكة', 'مياه', 'تسريب', 'تسرب', 'ماسورة', 'حوض', 'حمام', 'مرحاض', 'بانيو', 'صرف', 'بالوعة', 'انسداد', 'سيفون', 'خلاط', 'صنابير',
            'plumbing', 'water', 'leak', 'pipe', 'drain', 'toilet', 'sink', 'faucet', 'shower', 'bath', 'sewage', 'clog',
        ],
        'air_conditioning' => [
            'تكييف', 'مكيف', 'تبريد', 'فريون', 'ضاغط', 'كمبروسور', 'ترموستات', 'حرارة', 'بارد', 'هواء', 'مروحة', 'سنترال',
            'ac', 'air conditioning', 'cooling', 'heating', 'thermostat', 'compressor', 'fan', 'vent', 'freon', 'temperature', 'cold',
        ],
        'painting' => [
            'دهان', 'دهانات', 'طلاء', 'بوية', 'لون', 'بوهية', 'جدار', 'حائط', 'صبغ', 'معجون',
            'paint', 'painting', 'wall', 'color', 'plaster', 'coating', 'primer',
        ],
        'carpentry' => [
            'نجارة', 'خشب', 'باب', 'شباك', 'دولاب', 'مطبخ', 'غرفة', 'أثاث', 'نافذة', 'درج', 'باركيه',
            'carpentry', 'wood', 'door', 'window', 'cabinet', 'furniture', 'floor', 'stairs', 'frame', 'shelf',
        ],
    ];

    /**
     * MaintenanceAIService constructor.
     */
    public function __construct()
    {
        $this->url = config('services.ai.url', 'http://localhost:8001');
    }

    /**
     * Classify a maintenance description text into a category.
     *
     * @param  string  $text
     * @return array
     */
    public function classify(string $text): array
    {
        try {
            $response = Http::timeout(5)
                ->post($this->url . '/classify', ['text' => $text]);

            if ($response->successful()) {
                $data = $response->json();
                $category = $data['category'] ?? null;
                $confidence = $data['confidence'] ?? null;

                if ($category && $confidence !== null) {
                    return [
                        'category' => $category,
                        'confidence' => $confidence,
                        'category_id' => $data['category_id'] ?? ($this->categoryIds[$category] ?? null),
                    ];
                }
            }

            Log::warning('AI service returned invalid response, using keyword fallback');
        } catch (\Exception $e) {
            Log::warning('AI maintenance service unavailable: ' . $e->getMessage());
        }

        return $this->keywordFallback($text);
    }

    /**
     * Get the category ID for a given category name.
     *
     * @param  string  $category
     * @return int|null
     */
    public function getCategoryId(string $category): ?int
    {
        return $this->categoryIds[$category] ?? null;
    }

    /**
     * Predict the next maintenance date for a property based on history.
     *
     * @param  int     $propertyId
     * @param  string  $category
     * @return array|null
     */
    public function predictNextMaintenance(int $propertyId, string $category): ?array
    {
        $history = MaintenanceRequest::where('property_id', $propertyId)
            ->whereNotNull('completed_at')
            ->whereNotNull('category_id')
            ->latest('completed_at')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'days_ago' => (int) round(abs(now()->diffInDays($r->completed_at))),
                'category_id' => $r->category_id,
            ])
            ->toArray();

        try {
            $response = Http::timeout(5)
                ->post($this->url . '/predict', [
                    'property_id' => $propertyId,
                    'history' => $history,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                MaintenancePrediction::where('property_id', $propertyId)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                $prediction = MaintenancePrediction::create([
                    'property_id' => $propertyId,
                    'predicted_category' => $data['predicted_category'] ?? $category,
                    'predicted_category_id' => $data['predicted_category_id'] ?? $this->categoryIds[$category],
                    'days_until_next' => $data['days_until_next'] ?? 30,
                    'predicted_date' => $data['predicted_date'] ?? now()->addDays(30)->toDateString(),
                    'is_active' => true,
                    'model_used' => $data['model_used'] ?? 'lstm',
                    'generated_at' => now(),
                ]);

                $property = $prediction->property;
                if ($property && $property->owner_id) {
                    App::setLocale('ar');
                    Notification::create([
                        'user_id' => $property->owner_id,
                        'title' => __('Predictive maintenance alert'),
                        'content' => __('AI predicts next maintenance for :property in :days days (:date)', [
                            'property' => $property->title,
                            'days' => $prediction->days_until_next,
                            'date' => $prediction->predicted_date->format('Y-m-d'),
                        ]),
                    ]);
                }

                return $data;
            }

            Log::warning('AI predict failed', [
                'property_id' => $propertyId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::warning('AI predict endpoint unavailable: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Fallback classification using keyword matching when AI service is unavailable.
     *
     * @param  string  $text
     * @return array
     */
    protected function keywordFallback(string $text): array
    {
        $scores = [];

        foreach ($this->keywords as $category => $words) {
            $score = 0;
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scores[$category] = $score;
            }
        }

        if (!empty($scores)) {
            arsort($scores);
            $best = key($scores);
            $maxScore = current($scores);
            $totalWords = count($this->keywords[$best]);
            $confidence = round($maxScore / max($totalWords, 1), 2);

            return [
                'category' => $best,
                'confidence' => $confidence,
                'category_id' => $this->categoryIds[$best] ?? null,
            ];
        }

        return ['category' => 'other', 'confidence' => 0.5, 'category_id' => $this->categoryIds['other']];
    }
}
