<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Plutu\Services\PlutuLocalBankCards;

/**
 * تحكم دفع Plutu (Web) — معالجة الدفع عبر Plutu LocalBankCards مع تأكيد وإلغاء وعودة
 */
class PlutuPaymentController extends Controller
{
    protected PlutuLocalBankCards $plutu;

    /**
     * Initialize the Plutu payment gateway with configured credentials.
     */
    public function __construct()
    {
        $this->plutu = new PlutuLocalBankCards;
        $this->plutu->setCredentials(
            config('plutu.api_key'),
            config('plutu.access_token'),
            config('plutu.secret_key')
        );
    }

    /**
     * Initiate payment for a booking via the Plutu gateway (web redirect).
     *
     * GET /plutu/pay/{booking}
     *
     * @param Booking $booking
     * @return RedirectResponse
     */
    public function pay(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            return back()->with('error', __('Unauthorized action'));
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('Cannot pay for this booking'));
        }

        $payment = Payment::firstOrCreate(
            ['booking_id' => $booking->id, 'status' => 'pending'],
            [
                'amount' => $booking->total_price,
                'status' => 'pending',
                'payment_type' => 'local_bank_cards',
            ]
        );

        if ($payment->status === 'completed') {
            return redirect()->route('bookings.show', $booking)->with('success', __('Already paid'));
        }

        $invoiceNo = 'INV-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
        $returnUrl = route('plutu.callback', ['booking' => $booking->id]);

        try {
            $apiResponse = $this->plutu->confirm($booking->total_price, $invoiceNo, $returnUrl, null, app()->getLocale());

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {
                $redirectUrl = $apiResponse->getRedirectUrl();
                return redirect()->away($redirectUrl);
            }

            $originalError = $apiResponse->getOriginalResponse()->getErrorMessage() ?? __('Payment gateway connection failed');
            if (preg_match('/amount|exceeded|maximum|max/i', $originalError)) {
                $error = __('Amount exceeded the maximum amount allowed for a transaction');
            } else {
                $error = $originalError;
            }
            $payment->update(['status' => 'failed']);
            return back()->with('error', $error);

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return back()->with('error', __('Payment gateway connection error'));
        }
    }

    /**
     * Handle the callback from the Plutu payment gateway.
     *
     * GET /plutu/callback
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function callback(Request $request): View|RedirectResponse
    {
        $booking = Booking::findOrFail($request->booking);

        try {
            $callback = $this->plutu->callbackHandler($request->query());

            if ($callback->isApprovedTransaction()) {
                DB::transaction(function () use ($booking) {
                    $payment = Payment::where('booking_id', $booking->id)->latest()->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);
                    }

                    if ($booking->status === 'pending') {
                        $booking->update(['status' => 'confirmed']);
                        $booking->property->update(['status' => 'booked']);
                    }

                    $booking->load('property');

                    Notification::create([
                        'user_id' => $booking->user_id,
                        'title' => __('Payment successful'),
                        'content' => __('Your payment for :property has been completed.', ['property' => $booking->property->title]),
                    ]);

                    Notification::create([
                        'user_id' => $booking->property->owner_id,
                        'title' => __('Payment completed'),
                        'content' => __('Invoice :invoice paid successfully.', ['invoice' => '#' . $booking->id]),
                    ]);
                });

                $booking->load('property');

                return view('plutu.success', compact('booking'));
            }

            return $this->handleFailedCallback($booking);
        } catch (\Exception $e) {
            return $this->handleFailedCallback($booking);
        }
    }

    /**
     * Initiate payment via API (returns a redirect URL to the Plutu gateway).
     *
     * POST /api/plutu/initiate
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiInitiate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => __('Unauthorized')], 403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => __('Cannot pay for this booking')], 400);
        }

        $payment = Payment::firstOrCreate(
            ['booking_id' => $booking->id, 'status' => 'pending'],
            [
                'amount' => $booking->total_price,
                'status' => 'pending',
                'payment_type' => 'local_bank_cards',
            ]
        );

        if ($payment->status === 'completed') {
            return response()->json(['message' => __('Already paid'), 'paid' => true]);
        }

        $invoiceNo = 'INV-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
        $returnUrl = route('plutu.callback', ['booking' => $booking->id]);

        try {
            $apiResponse = $this->plutu->confirm($booking->total_price, $invoiceNo, $returnUrl, null, app()->getLocale());

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {
                $redirectUrl = $apiResponse->getRedirectUrl();
                return response()->json(['redirect_url' => $redirectUrl]);
            }

            $originalError = $apiResponse->getOriginalResponse()->getErrorMessage() ?? __('Payment failed');
            if (preg_match('/amount|exceeded|maximum|max/i', $originalError)) {
                $error = __('Amount exceeded the maximum amount allowed for a transaction');
            } else {
                $error = $originalError;
            }
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => $error, 'amount_exceeded' => true], 400);

        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => __('Payment gateway connection error')], 500);
        }
    }

    /**
     * Check the payment status for a booking via API.
     *
     * GET /api/plutu/check
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCheck(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);
        $payment = Payment::where('booking_id', $request->booking_id)->latest()->first();

        return response()->json([
            'paid' => $payment && $payment->status === 'completed',
            'status' => $payment?->status ?? 'none',
        ]);
    }

    /**
     * Handle a failed payment callback from the Plutu gateway.
     *
     * @param Booking $booking
     * @return View
     */
    private function handleFailedCallback(Booking $booking): View
    {
        $payment = Payment::where('booking_id', $booking->id)->latest()->first();
        if ($payment) {
            $payment->update(['status' => 'failed']);
        }
        return view('plutu.cancel', compact('booking'));
    }
}
