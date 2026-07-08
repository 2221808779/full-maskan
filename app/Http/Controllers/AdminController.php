<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Notification;
use App\Models\Specialty;
use App\Models\TechnicianProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * تحكم لوحة التحكم — إدارة العقارات والمستخدمين وطلبات الصيانة وإحصائيات النظام عبر API
 */
class AdminController extends Controller
{
    /**
     * قائمة المستخدمين — عرض جميع المستخدمين مع فلترة اختيارية
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return response()->json($users);
    }

    /**
     * حظر مستخدم — تعليق حساب مستخدم (لا يمكن حظر المشرفين)
     */
    public function banUser(Request $request, User $user): JsonResponse
    {
        if ($user->user_type === 'admin') {
            return response()->json(['message' => __('Cannot ban an admin')], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => __('User is already banned')], 409);
        }

        $reason = $request->input('reason', '');
        $updateData = [
            'status' => 'suspended',
            'ban_reason' => $reason ?: __('No reason provided'),
            'banned_at' => now(),
        ];

        if ($request->input('ban_type') === 'temporary' && $request->filled('banned_until')) {
            $updateData['banned_until'] = $request->input('banned_until');
        }

        $user->update($updateData);

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $details = $request->input('details', '');
        $content = __('Your account has been banned. Reason: :reason', ['reason' => $reason ?: __('No reason provided')]);
        if ($details) {
            $content .= "\n" . __('Additional details: :details', ['details' => $details]);
        }

        Notification::create([
            'user_id' => $user->id,
            'title' => __('Account banned'),
            'content' => $content,
        ]);

        return response()->json([
            'message' => __('User banned successfully'),
            'user' => $user,
        ]);
    }

    /**
     * إلغاء الحظر — إعادة تفعيل حساب المستخدم المحظور
     */
    public function unbanUser(User $user): JsonResponse
    {
        $user->update([
            'status' => 'active',
            'ban_reason' => null,
            'banned_at' => null,
            'banned_until' => null,
        ]);

        return response()->json([
            'message' => __('User unbanned successfully'),
            'user' => $user,
        ]);
    }

    /**
     * إنشاء مستخدم — إنشاء مستخدم جديد بواسطة المشرف
     */
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|regex:/^09[12348]\d{7}$/|unique:users',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:owner,tenant,technician',
            'specializations' => 'required_if:user_type,technician|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = 'active';

        $user = User::create($validated);

        if ($validated['user_type'] === 'technician') {
            $profile = TechnicianProfile::create([
                'user_id' => $user->id,
                'experience_years' => $validated['experience_years'] ?? null,
            ]);
            if (!empty($validated['specializations'])) {
                $profile->specializations()->attach($validated['specializations']);
            }
        }

        return response()->json([
            'message' => __('User created successfully'),
            'user' => $user,
        ], 201);
    }

    /**
     * تحديث مستخدم — تعديل بيانات مستخدم بواسطة المشرف
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|regex:/^09[12348]\d{7}$/|unique:users,phone,' . $user->id,
            'user_type' => 'sometimes|in:owner,tenant,technician',
            'status' => 'sometimes|in:active,inactive,suspended',
            'specializations' => 'nullable|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
        ]);

        $user->update($validated);

        if ($user->user_type === 'technician') {
            $profile = $user->technicianProfile;
            if (!$profile) {
                $profile = TechnicianProfile::create(['user_id' => $user->id]);
            }
            if ($request->has('experience_years')) {
                $profile->experience_years = $validated['experience_years'];
            }
            $profile->save();
            if ($request->has('specializations')) {
                $profile->specializations()->sync($validated['specializations'] ?? []);
            }
        }

        return response()->json([
            'message' => __('User updated successfully'),
            'user' => $user,
        ]);
    }

    /**
     * قائمة العقارات — عرض جميع العقارات مع فلترة اختيارية للمشرف
     */
    public function properties(Request $request): JsonResponse
    {
        $query = Property::with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('property_type', $request->type);
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        $properties = $query->latest()->paginate(20);

        return response()->json($properties);
    }

    /**
     * الموافقة على عقار — تغيير حالة العقار من معلق إلى متاح
     */
    public function approveProperty(Property $property): JsonResponse
    {
        $property->update(['status' => 'available']);

        return response()->json([
            'message' => __('Property approved successfully'),
            'property' => $property,
        ]);
    }

    /**
     * رفض عقار — تغيير حالة العقار من معلق إلى غير متاح
     */
    public function rejectProperty(Property $property): JsonResponse
    {
        $property->update(['status' => 'unavailable']);

        return response()->json([
            'message' => __('Property rejected'),
            'property' => $property,
        ]);
    }

    /**
     * قائمة الحجوزات — عرض جميع الحجوزات للمشرف
     */
    public function bookings(): JsonResponse
    {
        $bookings = Booking::with('user', 'property')
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }

    /**
     * قائمة الصيانة — عرض جميع طلبات الصيانة للمشرف
     */
    public function maintenanceRequests(): JsonResponse
    {
        $requests = MaintenanceRequest::with('property', 'technician')
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * التقارير — إحصائيات لوحة التحكم (المستخدمين، العقارات، الحجوزات)
     */
    public function reports(): JsonResponse
    {
        $data = Cache::remember('admin:reports', 600, function () {
            $totalUsers = User::count();
            $totalProperties = Property::count();
            $totalBookings = Booking::count();
            $totalMaintenance = MaintenanceRequest::count();

            $usersByRole = User::selectRaw('user_type, count(*) as count')
                ->groupBy('user_type')
                ->pluck('count', 'user_type');

            $propertiesByStatus = Property::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $bookingsByStatus = Booking::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $recentBookings = Booking::with('user', 'property')
                ->latest()
                ->take(10)
                ->get();

            return [
                'totals' => [
                    'users' => $totalUsers,
                    'properties' => $totalProperties,
                    'bookings' => $totalBookings,
                    'maintenance_requests' => $totalMaintenance,
                ],
                'users_by_role' => $usersByRole,
                'properties_by_status' => $propertiesByStatus,
                'bookings_by_status' => $bookingsByStatus,
                'recent_bookings' => $recentBookings,
            ];
        });

        return response()->json($data);
    }
}
