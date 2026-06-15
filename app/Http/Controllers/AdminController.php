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

/**
 * تحكم لوحة التحكم — إدارة العقارات والمستخدمين وطلبات الصيانة وإحصائيات النظام عبر API
 */
class AdminController extends Controller
{
    /**
     * List all users with optional filters (user_type, status, search).
     *
     * GET /api/admin/users
     *
     * @param Request $request
     * @return JsonResponse
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
     * Ban a user (suspend account). Admins cannot be banned.
     *
     * POST /api/admin/users/{user}/ban
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
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
     * Unban a user (reactivate account).
     *
     * POST /api/admin/users/{user}/unban
     *
     * @param User $user
     * @return JsonResponse
     */
    public function unbanUser(User $user): JsonResponse
    {
        $user->update(['status' => 'active']);

        return response()->json([
            'message' => __('User unbanned successfully'),
            'user' => $user,
        ]);
    }

    /**
     * Create a new user by an admin.
     *
     * POST /api/admin/users
     *
     * @param Request $request
     * @return JsonResponse
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
     * Update a user's details by an admin.
     *
     * PUT /api/admin/users/{user}
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
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
     * List all properties with optional filters (status, type, owner_id).
     *
     * GET /api/admin/properties
     *
     * @param Request $request
     * @return JsonResponse
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
     * Approve a pending property listing.
     *
     * POST /api/admin/properties/{property}/approve
     *
     * @param Property $property
     * @return JsonResponse
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
     * Reject a pending property listing.
     *
     * POST /api/admin/properties/{property}/reject
     *
     * @param Property $property
     * @return JsonResponse
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
     * List all bookings.
     *
     * GET /api/admin/bookings
     *
     * @return JsonResponse
     */
    public function bookings(): JsonResponse
    {
        $bookings = Booking::with('user', 'property')
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }

    /**
     * List all maintenance requests.
     *
     * GET /api/admin/maintenance
     *
     * @return JsonResponse
     */
    public function maintenanceRequests(): JsonResponse
    {
        $requests = MaintenanceRequest::with('property', 'technician')
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * Get admin dashboard reports with aggregated statistics (users, properties, bookings).
     *
     * GET /api/admin/reports
     *
     * @return JsonResponse
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
