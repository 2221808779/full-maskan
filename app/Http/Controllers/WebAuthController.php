<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * تحكم المصادقة (Web) — تسجيل الدخول والتسجيل والتحقق عبر OTP للمسؤولين والمالكين
 */
class WebAuthController extends Controller
{
    /**
     * Redirect the authenticated user to their role-based dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectByRole()
    {
        $user = Auth::user();

        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->user_type === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->route('profile.index');
    }

    /**
     * Show the login form.
     * GET /login
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     * POST /login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['phone' => __('The provided credentials are incorrect.')])->onlyInput('phone');
        }

        if (in_array($user->user_type, ['tenant', 'technician'])) {
            return back()->withErrors(['phone' => __('This website is for administrators and property owners only')])->onlyInput('phone');
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
                    $msg .= ' ' . __('Reason') . ': ' . $reason;
                }
                return back()->withErrors(['phone' => $msg])->onlyInput('phone');
            }
        }

        if ($user->status === 'suspended') {
            $reason = $user->ban_reason ?? '';
            $msg = __('This account is not active, please contact administration');
            if ($reason) {
                $msg .= ' ' . __('Reason') . ': ' . $reason;
            }
            return back()->withErrors(['phone' => $msg])->onlyInput('phone');
        }

        if (!$user->phone_verified_at) {
            // Auto-verify existing active users (pre-OTP migration)
            if ($user->status === 'active') {
                $user->update(['phone_verified_at' => now()]);
            } else {
                return back()->with([
                    'phone_unverified' => true,
                    'unverified_phone' => $user->phone,
                ])->withErrors(['phone' => __('Please verify your phone number first')])->onlyInput('phone');
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    /**
     * Show the registration form.
     * GET /register
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request.
     * POST /register
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|regex:/^09[12348]\d{7}$/|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'role' => 'required|in:owner',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'user_type' => $validated['role'],
            'status' => 'inactive',
        ]);

        Auth::login($user);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'requires_otp' => true,
                'phone' => $user->phone,
            ]);
        }

        return redirect()->route('verification.notice');
    }

    /**
     * Log the user out and invalidate the session.
     * POST /logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the forgot password form.
     * GET /forgot-password
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showForgotForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.forgot-password');
    }

    /**
     * Send an OTP for password reset to the user's phone.
     * POST /forgot-password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/|exists:users,phone',
        ]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp_' . $validated['phone'], $otp, now()->addMinutes(5));

        $sent = false;
        try {
            $service = app(\App\Services\WhatsAppService::class);
            if (!empty(config('services.whatsapp.token'))) {
                $sent = $service->sendOtp($validated['phone'], $otp);
            }
        } catch (\Exception $e) {
            Log::error('OTP send failed', ['phone' => $validated['phone'], 'error' => $e->getMessage()]);
        }

        if (config('app.env') !== 'production') {
            Log::info('Password reset OTP for ' . $validated['phone'] . ': ' . $otp);
        }

        return redirect()->route('password.reset', ['phone' => $validated['phone']])
            ->with('success', __('OTP sent successfully'));
    }

    /**
     * Show the password reset form with the given phone number.
     * GET /reset-password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetForm(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        $phone = $request->query('phone');
        if (!$phone) {
            return redirect()->route('password.forgot');
        }

        return view('auth.reset-password', compact('phone'));
    }

    /**
     * Reset the user's password after OTP verification.
     * POST /reset-password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => 'required|regex:/^09[12348]\d{7}$/|exists:users,phone',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $cached = Cache::get('otp_' . $validated['phone']);

        if (!$cached || $cached !== $validated['otp']) {
            return back()->withErrors(['otp' => __('Invalid or expired OTP')])->onlyInput('phone', 'otp');
        }

        Cache::forget('otp_' . $validated['phone']);

        $user = User::where('phone', $validated['phone'])->first();
        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('login')
            ->with('success', __('Password reset successfully'));
    }
}
