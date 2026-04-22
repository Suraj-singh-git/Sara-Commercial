<?php

namespace App\Jobs;

use App\Services\Notification\TwilioMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendUserMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 90];

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public string $phone,
        public string $message,
        public string $channel,
        public array $context = [],
    ) {
        $this->onQueue('notifications');
    }

    public function handle(TwilioMessagingService $twilio): void
    {
        if (! $twilio->isConfigured()) {
            Log::warning('SendUserMessageJob skipped; Twilio not configured', $this->context);

            return;
        }

        $ok = $twilio->send($this->phone, $this->message, $this->channel, $this->context);
        if (! $ok) {
            throw new RuntimeException("Twilio {$this->channel} send failed");
        }
    }
}

