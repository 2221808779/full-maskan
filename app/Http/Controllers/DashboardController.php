<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * تحكم لوحة التحكم الرئيسية (Web) — عرض إحصائيات دور المستخدم والرسوم البيانية والاتجاهات
 */
class DashboardController extends Controller
{
    /**
     * لوحة التحكم الرئيسية — عرض إحصائيات ورسوم بيانية حسب دور المستخدم
     */
    public function index()
    {
        $user = Auth::user();
        $stats = [];
        $pendingProperties = collect();
        $deltas = [];

        if (in_array($user->user_type, ['admin', 'owner'])) {
            $query = Property::query();
            if ($user->user_type === 'owner') {
                $query->where('owner_id', $user->id);
            }

            $stats['properties_count'] = (clone $query)->count();
            $stats['available'] = (clone $query)->where('status', 'available')->count();
            $stats['booked'] = (clone $query)->where('status', 'booked')->count();
            $stats['maintenance'] = (clone $query)->where('status', 'maintenance')->count();
            $stats['pending_count'] = (clone $query)->where('status', 'pending')->count();

            $bookingQuery = Booking::query();
            if ($user->user_type === 'owner') {
                $bookingQuery->whereHas('property', fn($q) => $q->where('owner_id', $user->id));
            }
            $stats['bookings_count'] = (clone $bookingQuery)->count();
            $stats['bookings_pending'] = (clone $bookingQuery)->where('status', 'pending')->count();

            $stats['total_revenue'] = (clone $bookingQuery)
                ->where('status', 'completed')
                ->sum('total_price');

            $stats['maintenance_count'] = MaintenanceRequest::whereHas('property', function ($q) use ($user) {
                if ($user->user_type === 'owner') {
                    $q->where('owner_id', $user->id);
                }
            })->count();

            $stats['maintenance_pending'] = MaintenanceRequest::whereHas('property', function ($q) use ($user) {
                if ($user->user_type === 'owner') {
                    $q->where('owner_id', $user->id);
                }
            })->where('status', 'pending')->count();

            // Trend deltas (this month vs last month)
            $now = Carbon::now();
            $thisMonth = [(clone $now)->startOfMonth(), (clone $now)->endOfMonth()];
            $lastMonth = [(clone $now)->subMonth()->startOfMonth(), (clone $now)->subMonth()->endOfMonth()];

            $tmQuery = clone $bookingQuery;
            $lmQuery = clone $bookingQuery;
            $deltas['bookings'] = $this->calcDelta(
                (clone $tmQuery)->whereBetween('created_at', $thisMonth)->count(),
                (clone $lmQuery)->whereBetween('created_at', $lastMonth)->count()
            );

            $tmRev = clone $bookingQuery;
            $lmRev = clone $bookingQuery;
            $deltas['revenue'] = $this->calcDelta(
                (clone $tmRev)->where('status', 'completed')->whereBetween('created_at', $thisMonth)->sum('total_price'),
                (clone $lmRev)->where('status', 'completed')->whereBetween('created_at', $lastMonth)->sum('total_price')
            );

            $tmProp = clone $query;
            $lmProp = clone $query;
            $deltas['properties'] = $this->calcDelta(
                (clone $tmProp)->whereBetween('created_at', $thisMonth)->count(),
                (clone $lmProp)->whereBetween('created_at', $lastMonth)->count()
            );

            $recentBookings = (clone $bookingQuery)
                ->with('user', 'property')
                ->latest()
                ->take(10)
                ->get();

            if ($user->user_type === 'admin') {
                $pendingProperties = (clone $query)->where('status', 'pending')->with('owner')->latest()->take(5)->get();
            }
        } else {
            $stats['bookings_count'] = Booking::where('user_id', $user->id)->count();
            $stats['bookings_pending'] = Booking::where('user_id', $user->id)->where('status', 'pending')->count();
            $stats['maintenance_count'] = MaintenanceRequest::where('tenant_id', $user->id)->count();
            $stats['maintenance_pending'] = MaintenanceRequest::where('tenant_id', $user->id)->where('status', 'pending')->count();
            $stats['total_revenue'] = 0;
            $stats['properties_count'] = 0;
            $stats['available'] = $stats['booked'] = $stats['maintenance'] = $stats['pending_count'] = 0;

            $recentBookings = Booking::with('property')
                ->where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();
        }

        $usersByRole = [];
        if ($user->user_type === 'admin') {
            $usersByRole = User::selectRaw('user_type, count(*) as count')
                ->groupBy('user_type')
                ->pluck('count', 'user_type')
                ->toArray();
            foreach ($usersByRole as $type => $count) {
                $stats[$type . 's'] = $count;
            }
        }

        $chartMonths = [];
        $chartBookings = [];
        $chartRevenue = [];

        if (in_array($user->user_type, ['admin', 'owner'])) {
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $chartMonths[] = __($month->format('M'));

                $start = (clone $month)->startOfMonth();
                $end = (clone $month)->endOfMonth();

                $monthQuery = clone $bookingQuery;
                $chartBookings[] = (clone $monthQuery)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                $monthRevenue = clone $bookingQuery;
                $chartRevenue[] = (clone $monthRevenue)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total_price');
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $chartMonths[] = __($month->format('M'));
                $chartBookings[] = 0;
                $chartRevenue[] = 0;
            }
        }

        return view('dashboard.index', compact('stats', 'recentBookings', 'chartMonths', 'chartBookings', 'chartRevenue', 'pendingProperties', 'deltas', 'usersByRole'));
    }

    /**
     * حساب الفارق — حساب نسبة التغير بين قيمتين (الاتجاهات الشهرية)
     */
    private function calcDelta($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? ['pct' => 100, 'dir' => 'up'] : ['pct' => 0, 'dir' => 'up'];
        }
        $pct = round((($current - $previous) / $previous) * 100);
        return ['pct' => abs($pct), 'dir' => $pct >= 0 ? 'up' : 'down'];
    }
}
