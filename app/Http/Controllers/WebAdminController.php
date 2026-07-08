<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Specialty;
use App\Models\TechnicianProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * تحكم الإدارة (Web) — إدارة المستخدمين والعقارات وطلبات الصيانة والإعدادات عبر واجهة الويب
 */
class WebAdminController extends Controller
{
    /**
     * قائمة المستخدمين — عرض المستخدمين مع فلترة حسب النوع والحالة والبحث
     */
    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * عرض مستخدم — تفاصيل مستخدم محدد مع عقاراته وحجوزاته
     */
    public function showUser(User $user): View
    {
        $user->load('properties', 'bookings.property');
        return view('admin.users.show', compact('user'));
    }

    /**
     * نموذج إنشاء مستخدم — عرض صفحة إضافة مستخدم جديد
     */
    public function createUser(): View
    {
        $specialties = Specialty::all();
        return view('admin.users.create', compact('specialties'));
    }

    /**
     * حفظ المستخدم — إنشاء مستخدم جديد وحفظه في قاعدة البيانات
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|regex:/^09[12348]\d{7}$/|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:owner,tenant,technician',
            'specializations' => 'required_if:user_type,technician|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
        ]);

        if ($validated['user_type'] === 'technician') {
            $profile = TechnicianProfile::create([
                'user_id' => $user->id,
                'experience_years' => $validated['experience_years'] ?? null,
            ]);
            if (!empty($validated['specializations'])) {
                $profile->specializations()->attach($validated['specializations']);
            }
        }

        return redirect()->route('admin.users')->with('success', __('User created successfully'));
    }

    /**
     * نموذج تعديل مستخدم — عرض صفحة تعديل بيانات المستخدم
     */
    public function editUser(User $user): View
    {
        $specialties = Specialty::all();
        return view('admin.users.edit', compact('user', 'specialties'));
    }

    /**
     * تحديث مستخدم — تعديل بيانات مستخدم مع إشعاره بالتحديث
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|regex:/^09[12348]\d{7}$/|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'user_type' => 'sometimes|in:owner,tenant,technician',
            'specializations' => 'nullable|array',
            'specializations.*' => 'exists:specialties,id',
            'experience_years' => 'nullable|integer|min:0|max:70',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

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

        Notification::create([
            'user_id' => $user->id,
            'title' => __('Your account data updated'),
            'content' => __('Admin updated your profile data on the platform.'),
        ]);

        return redirect()->route('admin.users')->with('success', __('User updated successfully'));
    }

    /**
     * حظر مستخدم — تعليق حساب المستخدم مع سبب و مدة اختيارية
     */
    public function banUser(Request $request, User $user): RedirectResponse
    {
        if ($user->user_type === 'admin') {
            return back()->with('error', __('Cannot ban an admin'));
        }

        if ($user->status === 'suspended') {
            return back()->with('error', __('User is already banned'));
        }

        $reason = $request->input('reason', '') ?: $request->input('reason_type', '');
        if (empty($reason)) {
            return back()->with('error', __('Please enter a ban reason'));
        }

        $updateData = [
            'status' => 'suspended',
            'ban_reason' => $reason,
            'banned_at' => now(),
        ];

        if ($request->input('ban_type') === 'temporary' && $request->filled('banned_until')) {
            $updateData['banned_until'] = $request->input('banned_until');
        }

        $user->update($updateData);

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $details = $request->input('details', '');
        $content = __('Your account has been banned. Reason: :reason', ['reason' => $reason]);
        if ($details) {
            $content .= "\n" . __('Additional details: :details', ['details' => $details]);
        }

        Notification::create([
            'user_id' => $user->id,
            'title' => __('Account banned'),
            'content' => $content,
        ]);

        return back()->with('success', __('User banned successfully'));
    }

    /**
     * إلغاء الحظر — إعادة تفعيل حساب المستخدم المحظور
     */
    public function unbanUser(User $user): RedirectResponse
    {
        $user->update([
            'status' => 'active',
            'ban_reason' => null,
            'banned_at' => null,
            'banned_until' => null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => __('Account unbanned'),
            'content' => __('Your account has been unbanned and access restored.'),
        ]);

        return back()->with('success', __('User unbanned successfully'));
    }

    /**
     * حذف مستخدم — حذف حساب المستخدم وجميع بياناته المرتبطة
     */
    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->user_type === 'admin') {
            return back()->with('error', __('Cannot delete an admin'));
        }

        $activeBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->count();

        $pendingPayments = \App\Models\Payment::whereHas('booking', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->count();

        if ($activeBookings > 0) {
            return back()->with('error', __('Cannot delete account due to active bookings. Cancel them first.'));
        }

        if ($pendingPayments > 0) {
            return back()->with('error', __('Cannot delete account due to pending payments. Settle them first.'));
        }

        $user->bookings()->delete();
        $user->notifications()->delete();
        $user->properties()->each(function ($property) {
            $property->maintenanceRequests()->delete();
            $property->bookings()->delete();
            $property->reviews()->delete();
            $property->favorites()->delete();
            $property->delete();
        });
        $user->delete();

        return back()->with('success', __('User and all associated data deleted successfully'));
    }

    /**
     * قائمة العقارات — عرض العقارات مع فلترة حسب الحالة والنوع للمشرف
     */
    public function properties(Request $request): View
    {
        $query = Property::with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        $properties = $query->latest()->paginate(20);

        return view('admin.properties', compact('properties'));
    }

    /**
     * العقارات المعلقة — عرض العقارات التي تنتظر مراجعة المشرف
     */
    public function pendingProperties(Request $request): View
    {
        $properties = Property::with('owner')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.properties_pending', compact('properties'));
    }

    /**
     * مراجعة عقار — عرض تفاصيل عقار لمراجعته من قبل المشرف
     */
    public function reviewProperty(Property $property): View
    {
        $property->load('owner', 'bookings.user');
        return view('admin.properties_review', compact('property'));
    }

    /**
     * طلب مراجعة — إرسال العقار للمشرف للموافقة
     */
    public function requestApproval(Property $property): RedirectResponse
    {
        $property->update(['status' => 'pending']);

        Notification::create([
            'user_id' => $property->owner_id,
            'title' => __('Property sent for review'),
            'content' => __('Your property :property has been sent for admin review.', ['property' => $property->title]),
        ]);

        return redirect()->route('admin.properties')->with('success', __('Property sent for review'));
    }

    /**
     * الموافقة على عقار — تغيير حالة العقار إلى متاح ونشره
     */
    public function approveProperty(Request $request, Property $property): RedirectResponse
    {
        $property->update(['status' => 'available']);

        Notification::create([
            'user_id' => $property->owner_id,
            'title' => __('Property approved'),
            'content' => __('Your property :property has been approved and published.', ['property' => $property->title]),
        ]);

        return redirect()->route('admin.properties.pending')->with('success', __('Property approved for publishing'));
    }

    /**
     * رفض عقار — رفض العقار وإعلام المالك بسبب الرفض
     */
    public function rejectProperty(\App\Http\Requests\RejectPropertyRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();

        $property->update(['status' => 'unavailable']);

        Notification::create([
            'user_id' => $property->owner_id,
            'title' => __('Property rejected'),
            'content' => __('Your property :property was not approved. Reason: :reason', ['property' => $property->title, 'reason' => $validated['reason']]),
        ]);

        return redirect()->route('admin.properties.pending')->with('success', __('Property rejected'));
    }

    /**
     * إلغاء تنشيط عقار — تعيين العقار كغير متاح
     */
    public function deactivateProperty(Property $property): RedirectResponse
    {
        $property->update(['status' => 'unavailable']);

        return back()->with('success', __('Property deactivated'));
    }

    /**
     * قائمة الحجوزات — عرض الحجوزات مع فلترة حسب الحالة للمشرف
     */
    public function bookings(Request $request): View
    {
        $query = Booking::with('user', 'property.owner', 'payment');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(20);

        return view('admin.bookings', compact('bookings'));
    }

    /**
     * قائمة الصيانة — عرض طلبات الصيانة مع فلترة للمشرف
     */
    public function maintenanceRequests(Request $request): View
    {
        $query = MaintenanceRequest::with('property', 'technician', 'property.owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(20);

        return view('admin.maintenance', compact('requests'));
    }

    /**
     * التقارير — عرض إحصائيات النظام والإيرادات والرسوم البيانية للمشرف
     */
    public function reports(): View
    {
        $totalUsers = User::count();
        $totalProperties = Property::count();
        $totalBookings = Booking::count();
        $totalMaintenance = MaintenanceRequest::count();
        $totalRevenue = Booking::where('status', 'completed')->sum('total_price');
        $pendingBookings = Booking::where('status', 'pending')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $usersByRole = User::selectRaw('user_type, count(*) as count')
            ->groupBy('user_type')
            ->pluck('count', 'user_type');

        $propertiesByStatus = Property::selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $bookingsByStatus = Booking::selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $recentBookings = Booking::with('user', 'property')
            ->latest()->take(10)->get();

        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = \Illuminate\Support\Carbon::now()->subMonths($i);
            $revenue = Booking::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_price');
            $monthlyRevenue[] = [
                'month' => __($month->format('M')),
                'amount' => $revenue,
            ];
        }

        return view('admin.reports.index', compact(
            'totalUsers', 'totalProperties', 'totalBookings', 'totalMaintenance',
            'totalRevenue', 'pendingBookings', 'completedBookings',
            'usersByRole', 'propertiesByStatus', 'bookingsByStatus',
            'recentBookings', 'monthlyRevenue'
        ));
    }

    /**
     * الإعدادات — عرض صفحة إعدادات الموقع
     */
    public function settings(): View
    {
        $settings = config('settings', []);

        return view('admin.settings', compact('settings'));
    }

    /**
     * حفظ الإعدادات — تحديث إعدادات الموقع (معلومات الاتصال والشروط)
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:500',
            'terms' => 'nullable|string|max:100000',
        ]);

        $this->writeSettingsConfig($validated);

        return redirect()->route('admin.settings')->with('success', __('Settings saved successfully'));
    }

    /**
     * كتابة الإعدادات — حفظ الإعدادات في ملف config/settings.php
     */
    private function writeSettingsConfig(array $settings): void
    {
        $allowedKeys = ['contact_phone', 'contact_email', 'contact_address', 'terms'];
        $data = [];
        foreach ($allowedKeys as $key) {
            $data[$key] = $settings[$key] ?? '';
        }

        $content = '<?php' . "\n\nreturn " . var_export($data, true) . ";\n";

        file_put_contents(config_path('settings.php'), $content);
    }

    // ========================
    // Archive
    // ========================

    /**
     * الأرشيف — عرض الحجوزات المؤرشفة (المكتملة + القديمة)
     */
    public function archive(Request $request): View
    {
        $archivedBookings = Booking::whereNotNull('archived_at')
            ->with('user', 'property')
            ->latest('archived_at')
            ->paginate(20);

        return view('admin.archive', compact('archivedBookings'));
    }

    /**
     * تشغيل الأرشفة — أرشفة الحجوزات المكتملة الأقدم من 6 أشهر
     */
    public function runArchive(Request $request): RedirectResponse
    {
        $count = Booking::where('status', 'completed')
            ->where('end_date', '<', now()->subMonths(6))
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        return redirect()->route('admin.archive')->with('success', $count
            ? __(':count old bookings archived', ['count' => $count])
            : __('No bookings eligible for archiving (need completed + end_date older than 6 months)'));
    }

    /**
     * استعادة من الأرشيف — إعادة الحجز المؤرشف إلى حالته الطبيعية
     */
    public function restoreArchive(Booking $booking): RedirectResponse
    {
        $booking->update(['archived_at' => null]);
        return redirect()->route('admin.archive')->with('success', __('Booking restored'));
    }

    // ========================
    // Broadcast
    // ========================

    /**
     * نموذج البث — صفحة إرسال إشعار جماعي لجميع المستخدمين
     */
    public function broadcastForm(): View
    {
        return view('admin.notifications.broadcast');
    }

    /**
     * إرسال البث — إرسال إشعار لجميع المستخدمين عدا المشرف
     */
    public function sendBroadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $admin = $request->user();
        $users = User::when($admin, fn($q) => $q->where('id', '!=', $admin->id))->get();
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);
        }

        return redirect()->route('admin.settings')->with('success', __('Broadcast sent to :count users', ['count' => $users->count()]));
    }

    // ========================
    // Cities
    // ========================

    /**
     * إدارة المدن — عرض صفحة إدارة المدن الليبية
     */
    public function cities(): View
    {
        $cities = config('cities.cities', []);
        return view('admin.cities', compact('cities'));
    }

    /**
     * إضافة مدينة — إضافة مدينة ليبية جديدة لقائمة المدن مع التحقق من صحتها
     */
    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($validated['name']);

        $libyanCities = [
            'طرابلس', 'بنغازي', 'مصراتة', 'الخمس', 'زليتن',
            'صبراتة', 'صرمان', 'العجيلات', 'الجميل', 'ركدالين',
            'الزاوية', 'غريان', 'يفرن', 'الأصابعة', 'ككلة',
            'الرجبان', 'الحشان', 'مزدة', 'نالوت', 'غدامس',
            'تاجوراء', 'جنزور', 'قصر بن غشير', 'درنة', 'طبرق',
            'البيضاء', 'شحات', 'المرج', 'القبة', 'الكفرة',
            'أجدابيا', 'سرت', 'رأس لانوف', 'سبها', 'مرزق',
            'أوباري', 'غات', 'ترهونة', 'بني وليد', 'زوارة',
            'العزيزية', 'هون', 'مردوم', 'تازربو', 'الجغبوب',
            'أوجلة', 'السائح', 'وادي الآجال', 'القطرون', 'الوشكة',
            'سلطان', 'التميمي', 'إمساعد', 'جخرة', 'البريقة',
            'توكرة', 'سوسة', 'الأبرق', 'وادي الشاطئ', 'السدرة',
            'triploli', 'tripoli', 'tripolis', 'benghazi', 'misrata', 'misurata',
        ];

        $found = false;
        $searchName = mb_strtolower($name);
        foreach ($libyanCities as $city) {
            if (mb_strtolower($city) === $searchName) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return back()->withErrors(['name' => __('Please enter a valid Libyan city name')])->withInput();
        }

        $cities = config('cities.cities', []);

        $cities[] = $name;

        $this->writeCitiesConfig($cities);

        return redirect()->route('admin.cities')->with('success', __('City added successfully'));
    }

    /**
     * حذف مدينة — إزالة مدينة من قائمة المدن
     */
    public function destroyCity(string $city): RedirectResponse
    {
        $cities = config('cities.cities', []);
        $found = false;
        $cities = array_values(array_filter($cities, function ($c) use ($city, &$found) {
            if ($c === $city) {
                $found = true;
                return false;
            }
            return true;
        }));

        if (!$found) {
            return redirect()->route('admin.cities')->with('error', __('City not found'));
        }

        $this->writeCitiesConfig($cities);

        return redirect()->route('admin.cities')->with('success', __('City deleted successfully'));
    }

    /**
     * كتابة المدن — حفظ قائمة المدن في ملف config/cities.php
     */
    private function writeCitiesConfig(array $cities): void
    {
        $content = '<?php' . "\n\nreturn " . var_export(['cities' => array_values($cities)], true) . ";\n";

        file_put_contents(config_path('cities.php'), $content);
    }
}