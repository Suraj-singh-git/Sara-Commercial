<?php

namespace Tests\Unit;

use App\Services\Notification\TwilioMessagingService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwilioMessagingServiceTest extends TestCase
{
    public function test_it_sends_sms_with_twilio_api_when_configured(): void
    {
        config()->set('services.twilio.account_sid', 'AC_TEST');
        config()->set('services.twilio.auth_token', 'token_test');
        config()->set('services.twilio.sms_from', '+15005550006');
        config()->set('services.twilio.default_country_code', '+91');

        Http::fake([
            'https://api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201),
        ]);

        $service = new TwilioMessagingService();
        $ok = $service->send('9876543210', 'hello', 'sms', ['event' => 'unit_test']);

        $this->assertTrue($ok);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/AC_TEST/Messages.json'
                && ($data['To'] ?? null) === '+919876543210'
                && ($data['From'] ?? null) === '+15005550006';
        });
    }

    public function test_it_returns_false_when_twilio_is_not_configured(): void
    {
        config()->set('services.twilio.account_sid', '');
        config()->set('services.twilio.auth_token', '');

        Http::fake();

        $service = new TwilioMessagingService();
        $ok = $service->send('9876543210', 'hello', 'sms');

        $this->assertFalse($ok);
        Http::assertNothingSent();
    }
}

