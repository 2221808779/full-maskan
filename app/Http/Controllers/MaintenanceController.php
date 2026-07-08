<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Specialty;
use App\Models\User;
use App\Services\MaintenanceAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * تحكم الصيانة (API) — إدارة طلبات الصيانة مع التصنيف الذكي عبر AI وتحديث الحالات للمستأجرين والفنيين
 */
class MaintenanceController extends Controller
{
    /**
     * قائمة الصيانة — عرض طلبات الصيانة حسب دور المستخدم عبر API
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $requests = MaintenanceRequest::with('property', 'technician', 'tenant');

        if ($user->user_type === 'technician') {
            $requests = $requests->where('technician_id', $user->id);
        } elseif ($user->user_type === 'owner') {
            $requests = $requests->whereHas('property', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        } else {
            $requests = $requests->where('tenant_id', $user->id);
        }

        $requests = $requests->latest()->paginate(20);

        return response()->json($requests);
    }

    /**
     * إنشاء طلب صيانة — تقديم طلب صيانة جديد مع تصنيف AI عبر API
     */
    public function store(Request $request, MaintenanceAIService $ai): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'problem_description' => 'required_without:description|string',
            'description' => 'string',
            'type' => 'nullable|string',
        ]);

        if (empty($validated['problem_description']) && !empty($validated['description'])) {
            $validated['problem_description'] = $validated['description'];
        }

        $property = Property::findOrFail($validated['property_id']);

        if ($property->owner_id !== $request->user()->id && $request->user()->user_type !== 'tenant') {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $userType = $validated['type'] ?? null;
        $aiResult = $ai->classify($validated['problem_description']);

        if ($userType && $aiResult['confidence'] < 0.7) {
            $typeMap = ['كهرباء' => 'electricity', 'سباكة' => 'plumbing', 'تكييف' => 'air_conditioning', 'دهانات' => 'painting', 'نجارة' => 'carpentry', 'أخرى' => 'other'];
            $mapped = $typeMap[$userType] ?? null;
            if ($mapped) {
                $aiResult = [
                    'category' => $mapped,
                    'confidence' => max($aiResult['confidence'], 0.75),
                    'category_id' => $ai->getCategoryId($mapped),
                ];
            }
        }

        $priority = $this->inferPriority($validated['problem_description']);

        $requestModel = DB::transaction(function () use ($validated, $request, $aiResult, $property, $priority) {
            $model = MaintenanceRequest::create([
                'property_id' => $validated['property_id'],
                'tenant_id' => $request->user()->id,
                'problem_description' => $validated['problem_description'],
                'ai_category' => $aiResult['category'],
                'ai_accuracy' => $aiResult['confidence'],
                'category' => $aiResult['category'],
                'category_id' => $aiResult['category_id'],
                'priority' => $priority,
                'status' => 'pending',
            ]);

            Notification::create([
                'user_id' => $property->owner_id,
                'title' => __('New maintenance request'),
                'content' => __('A maintenance request has been submitted for :property (:category).', [
                    'property' => $property->title,
                    'category' => __($aiResult['category']),
                ]),
            ]);

            $model->load('property', 'tenant', 'technician');

            return $model;
        });

        return response()->json([
            'message' => __('Maintenance request submitted successfully'),
            'maintenance_request' => $requestModel,
        ], 201);
    }

    /**
     * عرض طلب صيانة — تفاصيل طلب صيانة محدد عبر API
     */
    public function show(MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $maintenanceRequest->load('property', 'technician', 'tenant');

        return response()->json($maintenanceRequest);
    }

    /**
     * تعيين فني — إسناد طلب صيانة إلى فني (فقط المالك) عبر API
     */
    public function assignTechnician(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        if ($maintenanceRequest->property->owner_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $maintenanceRequest->load('property', 'tenant', 'technician');

        DB::transaction(function () use ($maintenanceRequest, $validated) {
            $maintenanceRequest->update([
                'technician_id' => $validated['technician_id'],
                'status' => 'assigned',
            ]);

            Notification::create([
                'user_id' => $validated['technician_id'],
                'title' => __('New maintenance task'),
                'content' => __('A maintenance task has been assigned to you for :property.', [
                    'property' => $maintenanceRequest->property->title,
                ]),
            ]);

            Notification::create([
                'user_id' => $maintenanceRequest->tenant_id,
                'title' => __('Technician assigned'),
                'content' => __('A technician has been assigned to your maintenance request for :property.', [
                    'property' => $maintenanceRequest->property->title,
                ]),
            ]);
        });

        return response()->json([
            'message' => __('Technician assigned successfully'),
            'maintenance_request' => $maintenanceRequest,
        ]);
    }

    /**
     * رفض طلب صيانة — رفض طلب صيانة مسند (فقط الفني) عبر API
     */
    public function rejectRequest(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        if ($maintenanceRequest->technician_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $maintenanceRequest->load('property', 'tenant');

        DB::transaction(function () use ($maintenanceRequest, $validated) {
            $maintenanceRequest->update([
                'status' => 'pending',
                'technician_id' => null,
                'technician_notes' => $validated['reason'] ?? null,
            ]);

            Notification::create([
                'user_id' => $maintenanceRequest->tenant_id,
                'title' => __('Maintenance request reopened'),
                'content' => __('The technician could not handle your request for :property. A new technician will be assigned.', [
                    'property' => $maintenanceRequest->property->title,
                ]),
            ]);
        });

        return response()->json([
            'message' => __('Maintenance request rejected'),
            'maintenance_request' => $maintenanceRequest,
        ]);
    }

    /**
     * تحديث حالة الصيانة — تغيير حالة طلب الصيانة (فقط الفني) عبر API
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        if ($maintenanceRequest->technician_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        $maintenanceRequest->load('property', 'tenant');

        if ($request->filled('status')) {
            $validated = $request->validate([
                'status' => 'required|in:in_progress,completed,cancelled',
                'technician_notes' => 'nullable|string',
            ]);

            $data = ['status' => $validated['status']];

            if ($validated['status'] === 'completed') {
                $data['completed_at'] = now();
                app(MaintenanceAIService::class)->predictNextMaintenance(
                    $maintenanceRequest->property_id,
                    $maintenanceRequest->ai_category ?? 'other'
                );
            }

            DB::transaction(function () use ($maintenanceRequest, $data, $validated) {
                $maintenanceRequest->update($data);

                Notification::create([
                    'user_id' => $maintenanceRequest->tenant_id,
                    'title' => __('Maintenance request :status', ['status' => __($validated['status'])]),
                    'content' => __('Your maintenance request for :property has been updated to :status.', [
                        'property' => $maintenanceRequest->property->title,
                        'status' => __($validated['status']),
                    ]),
                ]);

                Notification::create([
                    'user_id' => $maintenanceRequest->property->owner_id,
                    'title' => __('Maintenance :status', ['status' => __($validated['status'])]),
                    'content' => __('Maintenance request for :property updated to :status.', [
                        'property' => $maintenanceRequest->property->title,
                        'status' => __($validated['status']),
                    ]),
                ]);
            });
        } else {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:1000',
                'photos' => 'nullable|array',
                'photos.*' => 'nullable|string',
            ]);

            DB::transaction(function () use ($maintenanceRequest, $validated) {
                $maintenanceRequest->update([
                    'status' => 'completed',
                    'technician_notes' => $validated['notes'] ?? null,
                    'completed_at' => now(),
                ]);

                Notification::create([
                    'user_id' => $maintenanceRequest->tenant_id,
                    'title' => __('Maintenance completed'),
                    'content' => __('Your maintenance request for :property has been completed.', [
                        'property' => $maintenanceRequest->property->title,
                    ]),
                ]);

                Notification::create([
                    'user_id' => $maintenanceRequest->property->owner_id,
                    'title' => __('Maintenance completed'),
                    'content' => __('Maintenance request for :property has been completed.', [
                        'property' => $maintenanceRequest->property->title,
                    ]),
                ]);
            });
        }

        return response()->json([
            'message' => __('Maintenance request status updated'),
            'maintenance_request' => $maintenanceRequest,
        ]);
    }

    /**
     * الطلبات المعلقة — عرض طلبات الصيانة غير المسندة
     */
    public function pendingRequests(Request $request): JsonResponse
    {
        $requests = MaintenanceRequest::with('property', 'tenant')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * قبول طلب صيانة — قبول طلب صيانة معلق من قبل الفني
     */
    public function claimRequest(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        if ($maintenanceRequest->status !== 'pending') {
            return response()->json(['message' => __('This request is no longer available')], 400);
        }

        $maintenanceRequest->load('property', 'tenant');

        DB::transaction(function () use ($maintenanceRequest, $request) {
            $maintenanceRequest->update([
                'technician_id' => $request->user()->id,
                'status' => 'assigned',
            ]);

            Notification::create([
                'user_id' => $maintenanceRequest->property->owner_id,
                'title' => __('Technician assigned'),
                'content' => __('Technician :name has accepted the maintenance request for :property.', [
                    'name' => $request->user()->full_name,
                    'property' => $maintenanceRequest->property->title,
                ]),
            ]);

            Notification::create([
                'user_id' => $maintenanceRequest->tenant_id,
                'title' => __('Technician assigned'),
                'content' => __('A technician has been assigned to your maintenance request for :property.', [
                    'property' => $maintenanceRequest->property->title,
                ]),
            ]);
        });

        return response()->json([
            'message' => __('Maintenance request accepted'),
            'maintenance_request' => $maintenanceRequest,
        ]);
    }

    /**
     * استنتاج الأولوية — تحليل النص لتحديد مستوى أولوية طلب الصيانة
     */
    protected function inferPriority(string $text): string
    {
        $urgentKeywords = ['طوارئ', 'عاجل', 'طارئ', 'ضروري', 'مهم', 'حرق', 'تماس', 'تسرب', 'ماس كهربائي', 'ماس كهرباء', 'انقطاع', 'غرق', 'فيضان',
            'emergency', 'urgent', 'critical', 'important', 'fire', 'flood', 'gas leak', 'shock'];
        $highKeywords = ['كسر', 'انكسار', 'توقف', 'عطل', 'مشكلة', 'تلف', 'لا يعمل', 'خربان', 'عطلان', 'عطل', 'مشكلة',
            'broken', 'not working', 'damage', 'malfunction', 'defect'];

        $text = mb_strtolower($text);

        foreach ($urgentKeywords as $word) {
            if (str_contains($text, $word)) return 'urgent';
        }
        foreach ($highKeywords as $word) {
            if (str_contains($text, $word)) return 'high';
        }

        return 'medium';
    }

    /**
     * اقتراحات AI — عرض الفنيين المقترحين حسب تصنيف طلب الصيانة
     */
    public function getAiSuggestions(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $category = $maintenanceRequest->ai_category;
        if (!$category) {
            return response()->json(['suggestions' => []]);
        }

        $specialty = Specialty::where('name', $category)->first();
        if (!$specialty) {
            return response()->json(['suggestions' => []]);
        }

        $technicians = User::where('user_type', 'technician')
            ->whereHas('technicianProfile.specializations', function ($q) use ($specialty) {
                $q->where('specialization_id', $specialty->id);
            })
            ->with('technicianProfile')
            ->get()
            ->map(function ($user) {
                $profile = $user->technicianProfile;
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'phone' => $user->phone,
                    'avg_rating' => $profile ? $profile->avg_rating : 0,
                    'reviews_count' => $profile ? $profile->reviews_count : 0,
                    'experience_years' => $profile ? $profile->experience_years : null,
                ];
            })
            ->sortByDesc('avg_rating')
            ->values();

        return response()->json(['suggestions' => $technicians]);
    }

    /**
     * تغذية راجعة AI — إرسال تصحيح لتصنيف AI لطلب الصيانة
     */
    public function aiFeedback(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        $validated = $request->validate([
            'correct_category' => 'required|string|max:255',
        ]);

        $original = $maintenanceRequest->ai_category;
        $corrected = $validated['correct_category'];

        \Illuminate\Support\Facades\Log::info('AI feedback received', [
            'maintenance_request_id' => $maintenanceRequest->id,
            'original_category' => $original,
            'corrected_category' => $corrected,
        ]);

        $maintenanceRequest->update(['ai_category' => $corrected]);

        return response()->json(['message' => __('Feedback recorded')]);
    }

    /**
     * طلبات الفني — عرض طلبات الصيانة المسندة للفني المسجل
     */
    public function technicianRequests(Request $request): JsonResponse
    {
        $requests = MaintenanceRequest::with('property', 'tenant')
            ->where('technician_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }
}
