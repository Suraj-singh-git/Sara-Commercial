<?php

namespace Tests\Feature;

use App\Jobs\SendUserMessageJob;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_notifications_queue_sms_and_whatsapp_jobs(): void
    {
        Queue::fake();
        Mail::fake();

        $user = User::factory()->create([
            'phone' => '9990001111',
        ]);

        $service = app(NotificationService::class);
        $service->notifyUser($user, 'Subject', 'Body message', ['event' => 'queue_test']);

        Queue::assertPushed(SendUserMessageJob::class, 2);
    }

    public function test_otp_send_queues_one_channel_job(): void
    {
        Queue::fake();
        Mail::fake();

        $service = app(NotificationService::class);
        $service->otpSent('9990002222', 'whatsapp', '123456');

        Queue::assertPushed(SendUserMessageJob::class, function (SendUserMessageJob $job) {
            return $job->channel === 'whatsapp'
                && $job->phone === '9990002222';
        });
    }
}

