<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenancePrediction;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم المالك (Web) — عرض عقارات المالك وحجوزاته وتقارير PDF وإدارة الطلبات
 */
class OwnerController extends Controller
{
    /**
     * Display the owner dashboard with property stats, booking trends, and revenue chart data.
     *
     * GET /owner/dashboard
     *
     * @return View
     */
    public function dashboard()
    {
        $user = request()->user();

        $stats['properties_count'] = Property::where('owner_id', $user->id)->count();
        $stats['available'] = Property::where('owner_id', $user->id)->where('status', 'available')->count();
        $stats['booked'] = Property::where('owner_id', $user->id)->where('status', 'booked')->count();
        $stats['maintenance'] = Property::where('owner_id', $user->id)->where('status', 'maintenance')->count();
        $stats['pending_count'] = Property::where('owner_id', $user->id)->where('status', 'pending')->count();

        $bookingQuery = Booking::whereHas('property', fn($q) => $q->where('owner_id', $user->id));
        $stats['bookings_count'] = (clone $bookingQuery)->count();
        $stats['bookings_pending'] = (clone $bookingQuery)->where('status', 'pending')->count();
        $stats['bookings_active'] = (clone $bookingQuery)->whereIn('status', ['confirmed', 'in_progress', 'completed'])->count();
        $stats['total_revenue'] = (clone $bookingQuery)->where('status', 'completed')->sum('total_price');

        $stats['maintenance_pending'] = MaintenanceRequest::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->where('status', 'pending')->count();

        $now = \Illuminate\Support\Carbon::now();
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

        $recentBookings = (clone $bookingQuery)->with('user', 'property')->latest()->take(10)->get();

        $chartMonths = [];
        $chartBookings = [];
        $chartRevenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = \Illuminate\Support\Carbon::now()->subMonths($i);
            $chartMonths[] = __($month->format('M'));

            $start = (clone $month)->startOfMonth();
            $end = (clone $month)->endOfMonth();

            $chartBookings[] = (clone $bookingQuery)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $chartRevenue[] = (clone $bookingQuery)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_price');
        }

        return view('owner.dashboard', compact('stats', 'recentBookings', 'deltas', 'chartMonths', 'chartBookings', 'chartRevenue'));
    }

    /**
     * Calculate the percentage change between two values.
     *
     * @param float|int $current
     * @param float|int $previous
     * @return array
     */
    private function calcDelta($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? ['pct' => 100, 'dir' => 'up'] : ['pct' => 0, 'dir' => 'up'];
        }
        $pct = round((($current - $previous) / $previous) * 100);
        return ['pct' => abs($pct), 'dir' => $pct >= 0 ? 'up' : 'down'];
    }

    /**
     * List the authenticated owner's properties.
     *
     * GET /owner/properties
     *
     * @return View
     */
    public function properties()
    {
        $properties = Property::withCount('bookings')
            ->where('owner_id', request()->user()->id)
            ->latest()
            ->paginate(20);

        return view('owner.properties', compact('properties'));
    }

    /**
     * Toggle a property between available and unavailable. Only the owner can toggle.
     *
     * POST /owner/properties/{property}/toggle-status
     *
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function togglePropertyStatus(Request $request, Property $property): RedirectResponse
    {
        if ($property->owner_id !== $request->user()->id) {
            abort(403);
        }

        if ($property->status === 'available') {
            $activeBookings = Booking::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'in_progress'])
                ->count();
            if ($activeBookings > 0) {
                return back()->with('error', __('Cannot deactivate property due to active bookings'));
            }
            $property->update(['status' => 'unavailable']);
            return back()->with('success', __('Property deactivated and hidden from search results'));
        } else {
            $property->update(['status' => 'available']);
            return back()->with('success', __('Property reactivated and shown in search results'));
        }
    }

    /**
     * Show the availability calendar for a property with booked and blocked dates.
     *
     * GET /owner/properties/{property}/availability
     *
     * @param Property $property
     * @return View
     */
    public function availability(Property $property): View
    {
        if ($property->owner_id !== request()->user()->id) {
            abort(403);
        }

        $bookings = Booking::whereHas('property', fn($q) => $q->where('owner_id', request()->user()->id))
            ->where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get(['start_date', 'end_date']);

        $bookedDates = [];
        foreach ($bookings as $booking) {
            $start = \Carbon\Carbon::parse($booking->start_date);
            $end = \Carbon\Carbon::parse($booking->end_date);
            for ($d = clone $start; $d->lte($end); $d->addDay()) {
                $bookedDates[] = $d->format('Y-m-d');
            }
        }
        $bookedDates = array_unique($bookedDates);
        sort($bookedDates);

        $closedDates = $property->unavailable_dates ?? [];
        $upcomingBookings = Booking::with('user')
            ->where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->where('start_date', '>=', now()->subDays(1))
            ->orderBy('start_date')
            ->get();

        return view('owner.availability', compact('property', 'bookedDates', 'closedDates', 'upcomingBookings'));
    }

    /**
     * Store blocked/blackout dates for a property.
     *
     * POST /owner/properties/{property}/availability
     *
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function storeAvailability(Request $request, Property $property): RedirectResponse
    {
        if ($property->owner_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'dates' => 'required|string',
        ]);

        $dates = json_decode($request->dates, true);
        if (!is_array($dates)) {
            return back()->withErrors(['dates' => __('Invalid date format')]);
        }

        $existing = $property->unavailable_dates ?? [];
        $merged = array_values(array_unique(array_merge($existing, $dates)));
        $property->update(['unavailable_dates' => $merged]);

        return back()->with('success', __('Blocked dates saved'));
    }

    /**
     * Remove a blocked date from a property's availability.
     *
     * DELETE /owner/properties/{property}/availability
     *
     * @param Request $request
     * @param Property $property
     * @return RedirectResponse
     */
    public function removeAvailability(Request $request, Property $property): RedirectResponse
    {
        if ($property->owner_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate(['date' => 'required|date']);
        $existing = $property->unavailable_dates ?? [];
        $filtered = array_values(array_filter($existing, fn($d) => $d !== $request->date));
        $property->update(['unavailable_dates' => $filtered]);

        return back()->with('success', __('Date unblocked'));
    }

    /**
     * List bookings for the authenticated owner's properties, with optional status filter.
     *
     * GET /owner/bookings
     *
     * @return View
     */
    public function bookings()
    {
        $query = Booking::with('user', 'property', 'property.owner', 'payment')
            ->whereHas('property', fn($q) => $q->where('owner_id', request()->user()->id));

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        $bookings = $query->latest()->paginate(20);

        return view('owner.bookings', compact('bookings'));
    }

    /**
     * Show details of a single booking for the owner, including reviews.
     *
     * GET /owner/bookings/{booking}
     *
     * @param Booking $booking
     * @return View
     */
    public function showBooking(Booking $booking)
    {
        if ($booking->property->owner_id !== request()->user()->id) {
            abort(403);
        }

        $review = \App\Models\Review::where('property_id', $booking->property_id)
            ->where('user_id', $booking->user_id)
            ->first();

        return view('bookings.show', compact('booking', 'review'));
    }

    /**
     * List maintenance requests for the owner's properties, with AI predictions.
     *
     * GET /owner/maintenance
     *
     * @return View
     */
    public function maintenance()
    {
        $requests = MaintenanceRequest::with('property', 'technician', 'tenant')
            ->whereHas('property', fn($q) => $q->where('owner_id', request()->user()->id));

        if (request()->filled('status')) {
            $requests->where('status', request('status'));
        }

        $requests = $requests->latest()->paginate(20);

        $upcomingPredictions = MaintenancePrediction::whereHas('property', fn($q) => $q->where('owner_id', request()->user()->id))
            ->where('is_active', true)
            ->where('predicted_date', '>=', now())
            ->with('property')
            ->orderBy('predicted_date')
            ->take(10)
            ->get();

        $predictionsCount = $upcomingPredictions->count();

        return view('owner.maintenance', compact('requests', 'upcomingPredictions', 'predictionsCount'));
    }

    /**
     * Show details of a single maintenance request with suggested technicians.
     *
     * GET /owner/maintenance/{id}
     *
     * @param int $id
     * @return View
     */
    public function showMaintenance($id)
    {
        $maintenanceRequest = MaintenanceRequest::with('property', 'technician', 'tenant')->findOrFail($id);

        if (!$maintenanceRequest->property || $maintenanceRequest->property->owner_id !== request()->user()->id) {
            abort(403);
        }

        $review = $maintenanceRequest->technician_id
            ? \App\Models\Review::where('technician_id', $maintenanceRequest->technician_id)
                ->where('property_id', $maintenanceRequest->property_id)
                ->first()
            : null;

        $category = $maintenanceRequest->ai_category;
        $specialty = $category ? \App\Models\Specialty::where('name', $category)->first() : null;

        $technicians = User::where('user_type', 'technician')
            ->when($specialty, function ($q) use ($specialty) {
                $q->whereHas('technicianProfile.specializations', function ($sq) use ($specialty) {
                    $sq->where('specialization_id', $specialty->id);
                });
            })
            ->with('technicianProfile')
            ->get()
            ->sortByDesc(fn($u) => $u->technicianProfile?->avg_rating ?? 0);

        return view('maintenance.show', compact('maintenanceRequest', 'technicians', 'review', 'category'));
    }

    /**
     * Show a timeline view of bookings across the owner's properties.
     *
     * GET /owner/timeline
     *
     * @return View
     */
    public function timeline()
    {
        $properties = Property::where('owner_id', request()->user()->id)
            ->with(['bookings' => fn($q) => $q->with('user')
                ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
                ->where('start_date', '>=', now()->subDays(30))
                ->orderBy('start_date')
            ])
            ->get();

        return view('owner.timeline', compact('properties'));
    }

    /**
     * Show financial reports for the owner including revenue, monthly trends, and payment history.
     *
     * GET /owner/reports
     *
     * @return View
     */
    public function reports()
    {
        $user = request()->user();

        $totalRevenue = Booking::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->where('status', 'completed')
            ->sum('total_price');

        $completedBookings = Booking::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->where('status', 'completed')
            ->count();

        $totalProperties = Property::where('owner_id', $user->id)->count();
        $avgPrice = Property::where('owner_id', $user->id)->avg('price');

        $payments = \App\Models\Payment::whereHas('booking.property', fn($q) => $q->where('owner_id', $user->id))
            ->with('booking.property', 'booking.user')
            ->latest()
            ->paginate(20);

        // Build monthly revenue for last 6 months
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = (clone $month)->startOfMonth();
            $end = (clone $month)->endOfMonth();
            $monthlyRevenue[] = [
                'month' => __($month->format('M')),
                'amount' => Booking::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total_price'),
            ];
        }

        return view('owner.reports', compact(
            'totalRevenue', 'completedBookings', 'totalProperties', 'avgPrice',
            'payments', 'monthlyRevenue'
        ));
    }

    /**
     * List completed bookings with invoices for the owner's properties.
     *
     * GET /owner/invoices
     *
     * @return View
     */
    public function invoices()
    {
        $bookings = Booking::with('user', 'property', 'payment')
            ->whereHas('property', fn($q) => $q->where('owner_id', request()->user()->id))
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        return view('owner.invoices', compact('bookings'));
    }

    /**
     * Generate and download a PDF invoice for a completed booking.
     *
     * POST /owner/invoices/create
     *
     * @param Request $request
     * @return mixed
     */
    public function createInvoice(Request $request)
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);
        $booking = Booking::with('user', 'property.owner')->findOrFail($request->booking_id);

        if ($booking->property->owner_id !== $request->user()->id) {
            abort(403);
        }

        App::setLocale('ar');

        $html = view('invoice', compact('booking'))->render();

        $mpdf = new Mpdf([
            'mode' => 'ar',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output("invoice-{$booking->id}.pdf", 'D');
    }
}
