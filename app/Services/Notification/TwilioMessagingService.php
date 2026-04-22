<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioMessagingService
{
    public function isConfigured(): bool
    {
        return (string) config('services.twilio.account_sid', '') !== ''
            && (string) config('services.twilio.auth_token', '') !== '';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function send(string $phone, string $message, string $channel, array $context = []): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Twilio not configured, skipping send', array_merge($context, [
                'phone' => $phone,
                'channel' => $channel,
            ]));

            return false;
        }

        $sid = (string) config('services.twilio.account_sid');
        $token = (string) config('services.twilio.auth_token');
        $to = $this->formatE164($phone);
        $from = $channel === 'whatsapp'
            ? (string) config('services.twilio.whatsapp_from', '')
            : (string) config('services.twilio.sms_from', '');

        if ($to === '' || $from === '') {
            Log::warning('Twilio sender/recipient invalid', array_merge($context, [
                'phone' => $phone,
                'channel' => $channel,
                'to' => $to,
                'from' => $from,
            ]));

            return false;
        }

        $payload = [
            'From' => $channel === 'whatsapp' ? $this->asWhatsapp($from) : $from,
            'To' => $channel === 'whatsapp' ? $this->asWhatsapp($to) : $to,
            'Body' => $message,
        ];

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $res = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->timeout(20)
            ->post($url, $payload);

        if (! $res->successful()) {
            Log::warning('Twilio send failed', array_merge($context, [
                'channel' => $channel,
                'to' => $payload['To'],
                'status' => $res->status(),
                'response' => $res->body(),
            ]));

            return false;
        }

        Log::info('Twilio send success', array_merge($context, [
            'channel' => $channel,
            'to' => $payload['To'],
            'sid' => $res->json('sid'),
            'status' => $res->status(),
        ]));

        return true;
    }

    private function asWhatsapp(string $value): string
    {
        return str_starts_with($value, 'whatsapp:') ? $value : 'whatsapp:'.$value;
    }

    private function formatE164(string $phone): string
    {
        $trim = trim($phone);
        if ($trim === '') {
            return '';
        }

        $hasPlus = str_starts_with($trim, '+');
        $digits = preg_replace('/\D+/', '', $trim) ?: '';

        if ($digits === '') {
            return '';
        }

        if ($hasPlus) {
            return '+'.$digits;
        }

        $default = (string) config('services.twilio.default_country_code', '+91');
        $prefix = str_starts_with($default, '+') ? $default : '+'.$default;

        return $prefix.$digits;
    }
}

