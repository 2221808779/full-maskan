<?php

namespace App\Http\Controllers;

use App\Models\TechnicianProfile;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * تحكم المصادقة (API) — تسجيل الدخول والتسجيل والتحقق عبر OTP وإعادة تعيين كلمة المرور
 */
class AuthController extends Controller
{
    /**
     * تسجيل API — تسجيل مستخدم جديد (مستأجر أو فني) عبر الواجهة البرمجية
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|regex:/^09[12348]\d{7}$/|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:tenant,technician',
            'specializations' => 'required_if:role,technician|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
            'bio' => 'nullable|string|max:500',
        ]);

        $role = $validated['role'];
        unset($validated['role']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'inactive';
        $validated['user_type'] = $role;

        $user = User::create($validated);

        if ($role === 'technician') {
            $profile = TechnicianProfile::create([
                'user_id' => $user->id,
                'experience_years' => $validated['experience_years'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);
            $profile->specializations()->attach($validated['specializations']);
        }

        $token = null;

        $this->sendOtpToPhone($user->phone);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
                'requires_otp' => true,
            ],
        ], 201);
    }

    /**
     * تسجيل ويب — تسجيل مستخدم جديد (مالك) عبر الواجهة البصرية
     */
    public function registerWeb(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|regex:/^09[12348]\d{7}$/|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'role' => 'required|in:owner',
            'specializations' => 'required_if:role,technician|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
            'bio' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'birth_date' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'user_type' => $validated['role'],
            'status' => 'inactive',
        ]);

        if ($validated['role'] === 'technician') {
            $profile = TechnicianProfile::create([
                'user_id' => $user->id,
                'experience_years' => $validated['experience_years'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);
            $profile->specializations()->attach($validated['specializations']);
        }

        $token = null;

        $this->sendOtpToPhone($user->phone);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
                'requires_otp' => true,
            ],
        ], 201);
    }

    /**
     * تسجيل الدخول — تسجيل دخول المستخدم برقم الهاتف وكلمة المرور
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => [__('The provided credentials are incorrect.')],
            ]);
        }

        if (in_array($user->user_type, ['admin', 'owner'])) {
            return response()->json([
                'message' => __('This app is for tenants and technicians only'),
            ], 403);
        }

        if ($user->banned_at) {
            if ($user->banned_until && now()->greaterThan($user->banned_until)) {
                $user->update([
                    'status' => 'active',
                    'ban_reason' => null,
                    'banned_at' => null,
                    'banned_until' => null,
                ]);
            } else {
                if ($user->status !== 'suspended') {
                    $user->update(['status' => 'suspended']);
                }
                $reason = $user->ban_reason ?? '';
                $msg = __('This account is not active, please contact administration');
                if ($reason) {
                    $msg .= ' (' . __('Reason') . ': ' . $reason . ')';
                }
                return response()->json(['message' => $msg], 403);
            }
        }

        if ($user->status === 'suspended') {
            $reason = $user->ban_reason ?? '';
            $msg = __('This account is not active, please contact administration');
            if ($reason) {
                $msg .= ' (' . __('Reason') . ': ' . $reason . ')';
            }
            return response()->json(['message' => $msg], 403);
        }

        if (!$user->phone_verified_at) {
            // Auto-verify existing active users (pre-OTP migration)
            if ($user->status === 'active') {
                $user->update(['phone_verified_at' => now()]);
            } else {
                $this->sendOtpToPhone($user->phone);

                return response()->json([
                    'message' => __('Phone not verified. OTP sent.'),
                    'needs_otp' => true,
                    'phone' => $user->phone,
                ], 403);
            }
        }

        auth()->login($user);

        $request->session()->regenerate();

        return response()->json([
            'message' => __('Logged in successfully'),
            'user' => $user,
        ]);
    }

    /**
     * تسجيل الخروج — تسجيل خروج المستخدم وإلغاء الجلسة
     */
    public function logout(Request $request): JsonResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => __('Logged out successfully')]);
    }

    /**
     * الملف الشخصي — عرض بيانات المستخدم المسجل مع عقاراته
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()->load('properties')]);
    }

    /**
     * تحديث الملف — تعديل بيانات المستخدم الشخصية
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|regex:/^09[12348]\d{7}$/|unique:users,phone,' . $user->id,
            'profile_image' => 'sometimes|string|max:500',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => __('Profile updated successfully'),
            'user' => $user,
        ]);
    }

    /**
     * إرسال OTP — إرسال رمز التحقق إلى رقم الهاتف
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/',
        ]);

        $otp = $this->sendOtpToPhone($validated['phone']);

        $response = [
            'message' => __('OTP sent successfully'),
        ];

        return response()->json($response);
    }

    /**
     * تحقق OTP — التحقق من رمز OTP وتفعيل هاتف المستخدم
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/',
            'otp' => 'required|string|size:6',
        ]);

        $cached = Cache::get('otp_' . $validated['phone']);

        if (!$cached || $cached !== $validated['otp']) {
            return response()->json(['message' => __('Invalid or expired OTP')], 422);
        }

        Cache::forget('otp_' . $validated['phone']);

        $user = User::where('phone', $validated['phone'])->first();

        if ($user) {
            $user->update([
                'phone_verified_at' => now(),
                'status' => 'active',
            ]);

            try {
                auth()->login($user);
                $request->session()->regenerate();
            } catch (\Exception $e) {
                // API routes may not have session middleware — ignore
            }
        }

        return response()->json([
            'message' => __('Phone verified successfully'),
            'verified' => true,
        ]);
    }

    /**
     * إعادة تعيين كلمة المرور — استخدام الهاتف و OTP لإعادة تعيين كلمة المرور
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/|exists:users,phone',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $cached = Cache::get('otp_' . $validated['phone']);

        if (!$cached || $cached !== $validated['otp']) {
            return response()->json(['message' => __('Invalid or expired OTP')], 422);
        }

        Cache::forget('otp_' . $validated['phone']);

        $user = User::where('phone', $validated['phone'])->first();
        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => __('Password reset successfully')]);
    }

    /**
     * رفع صورة شخصية — رفع صورة الملف الشخصي للمستخدم
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->update(['profile_image' => $path]);
        }

        return response()->json([
            'message' => __('Profile photo updated'),
            'user' => $user,
        ]);
    }

    /**
     * حذف الصورة الشخصية — إزالة صورة الملف الشخصي
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['profile_image' => null]);

        return response()->json([
            'message' => __('Profile photo deleted'),
            'user' => $user,
        ]);
    }

    /**
     * إلغاء تنشيط الحساب — تعليق حساب المستخدم
     */
    public function deactivate(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['status' => 'suspended']);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => __('Account deactivated')]);
    }

    /**
     * حذف الحساب — حذف حساب المستخدم بشكل نهائي
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->delete();

        auth()->logout();
        $request->session()->invalidate();

        return response()->json(['message' => __('Account deleted permanently')]);
    }

    /**
     * إرسال OTP — توليد وإرسال رمز تحقق عبر واتساب
     */
    private function sendOtpToPhone(string $phone): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('otp_' . $phone, $otp, now()->addMinutes(5));

        $sent = false;
        try {
            $service = app(WhatsAppService::class);
            if (!empty(config('services.whatsapp.token'))) {
                $sent = $service->sendOtp($phone, $otp);
            }
        } catch (\Throwable $e) {
            Log::error('OTP send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
        }

        return $otp;
    }
}
