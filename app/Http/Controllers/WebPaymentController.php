<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم المدفوعات (Web) — عرض سجل المدفوعات عبر واجهة الويب
 */
class WebPaymentController extends Controller
{
    /**
     * Display a paginated list of payments for the authenticated user.
     * GET /payments
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->user_type === 'tenant') {
            $payments = Payment::whereHas('booking', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with('booking.property')->latest()->paginate(20);
        } elseif ($user->user_type === 'owner') {
            $query = Payment::with('booking.property');
            $query->whereHas('booking.property', fn($q) => $q->where('owner_id', $user->id));

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $payments = $query->latest()->paginate(20);
        } else {
            $payments = Payment::with('booking.property')->latest()->paginate(20);
        }

        return view('payments.index', compact('payments'));
    }
}
