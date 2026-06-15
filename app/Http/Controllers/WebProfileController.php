<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Specialty;
use App\Models\TechnicianProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * تحكم الملف الشخصي (Web) — عرض وتحديث الملف الشخصي والبيانات الإضافية للفنيين
 */
class WebProfileController extends Controller
{
    /**
     * Show the authenticated user's profile page.
     * GET /profile
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $specialties = Specialty::all();
        return view('profile.index', compact('specialties'));
    }

    /**
     * Update the authenticated user's profile data.
     * POST /profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|regex:/^09[12348]\d{7}$/|unique:users,phone,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'birth_date' => 'nullable|date',
        ];

        if ($user->user_type === 'technician') {
            $rules['experience_years'] = 'nullable|integer|min:0|max:70';
            $rules['bio'] = 'nullable|string|max:500';
            $rules['specializations'] = 'nullable|array';
            $rules['specializations.*'] = 'exists:specialties,id';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $validated['profile_image'] = $path;
        }

        $user->update($validated);

        if ($user->user_type === 'technician') {
            $profile = $user->technicianProfile;
            if (!$profile) {
                $profile = TechnicianProfile::create(['user_id' => $user->id]);
            }
            $profile->update([
                'experience_years' => $validated['experience_years'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);
            if (isset($validated['specializations'])) {
                $profile->specializations()->sync($validated['specializations']);
            }
        }

        return back()->with('success', __('Profile updated successfully'));
    }

    /**
     * Update the authenticated user's password.
     * POST /profile/password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function password(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', __('Password changed successfully'));
    }

    /**
     * Deactivate the authenticated user's account after confirming password.
     * POST /profile/deactivate
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!Hash::check($request->input('password', ''), $user->password)) {
            return back()->with('error', __('Incorrect password, deactivation failed'));
        }

        if ($user->user_type === 'owner') {
            $activeBookings = Booking::whereIn('property_id', Property::where('owner_id', $user->id)->pluck('id'))
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count();
            if ($activeBookings > 0) {
                return back()->with('error', __('Cannot deactivate due to active bookings on your properties'));
            }
        }

        if ($user->user_type === 'tenant') {
            $activeBookings = Booking::where('user_id', $user->id)
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count();
            if ($activeBookings > 0) {
                return back()->with('error', __('Cannot deactivate due to active bookings'));
            }
        }

        if ($user->user_type === 'technician') {
            $activeJobs = MaintenanceRequest::where('technician_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->count();
            if ($activeJobs > 0) {
                return back()->with('error', __('Cannot deactivate due to active maintenance requests'));
            }
        }

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', __('Account deactivated successfully'));
    }

    /**
     * Permanently delete the authenticated user's account after confirming password.
     * POST /profile/delete
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!Hash::check($request->input('password', ''), $user->password)) {
            return back()->with('error', __('Incorrect password, deletion failed'));
        }

        if ($user->user_type === 'owner') {
            $activeBookings = Booking::whereIn('property_id', Property::where('owner_id', $user->id)->pluck('id'))
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count();
            if ($activeBookings > 0) {
                return back()->with('error', __('Cannot delete account due to active bookings on your properties'));
            }
        }

        if ($user->user_type === 'tenant') {
            $activeBookings = Booking::where('user_id', $user->id)
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count();
            if ($activeBookings > 0) {
                return back()->with('error', __('Cannot delete account due to active bookings'));
            }
        }

        if ($user->user_type === 'technician') {
            $activeJobs = MaintenanceRequest::where('technician_id', $user->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->count();
            if ($activeJobs > 0) {
                return back()->with('error', __('Cannot delete account due to active maintenance requests'));
            }
        }

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('home')->with('success', __('Account permanently deleted'));
    }
}
