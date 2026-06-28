<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * خدمة واتساب — إرسال رسائل OTP عبر Facebook Graph API
 */
class WhatsAppService
{
    protected ?string $token = null;
    protected ?string $phoneNumberId = null;
    protected string $apiVersion;

    /**
     * WhatsAppService constructor.
     */
    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version', 'v18.0');
    }

    /**
     * Send a WhatsApp text message to a phone number.
     *
     * @param  string  $phone
     * @param  string  $message
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        $phone = ltrim($phone, '+');

        // Normalize Libyan phone numbers: 091xxxxxxx -> 21891xxxxxxx
        if (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '218' . substr($phone, 1);
        }

        try {
            $response = Http::withToken($this->token)
                ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send an OTP verification code via WhatsApp.
     *
     * @param  string  $phone
     * @param  string  $otp
     * @return bool
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "🔐 *Maskan* — رمز التحقق الخاص بك\n\n";
        $message .= "رمز التحقق: *{$otp}*\n\n";
        $message .= "صلاحية هذا الرمز لمدة 5 دقائق.\n";
        $message .= "إذا لم تطلب هذا الرمز، تجاهل هذه الرسالة.";

        return $this->send($phone, $message);
    }
}
