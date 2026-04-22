<?php

namespace App\Services\Notification;

use App\Jobs\SendUserMessageJob;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    public function orderPlaced(?Order $order): void
    {
        $this->log('order_placed', $order);

        $user = $order?->user;
        if ($user) {
            $this->notifyUser(
                $user,
                'Order placed',
                "Your order {$order->reference} has been placed successfully.",
                [
                    'event' => 'order_placed',
                    'order_id' => $order->id,
                    'reference' => $order->reference,
                ]
            );
        }
    }

    public function paymentSuccess(?Order $order): void
    {
        $this->log('payment_success', $order);

        $user = $order?->user;
        if ($user) {
            $this->notifyUser(
                $user,
                'Payment successful',
                "Payment received for order {$order->reference}.",
                [
                    'event' => 'payment_success',
                    'order_id' => $order->id,
                    'reference' => $order->reference,
                ]
            );
        }
    }

    public function orderDispatched(?Order $order): void
    {
        $this->log('order_dispatched', $order);

        $user = $order?->user;
        if ($user) {
            $this->notifyUser(
                $user,
                'Order shipped',
                "Good news! Your order {$order->reference} has been shipped.",
                [
                    'event' => 'order_shipped',
                    'order_id' => $order->id,
                    'reference' => $order->reference,
                ]
            );
        }
    }

    public function orderDelivered(?Order $order): void
    {
        $this->log('order_delivered', $order);

        $user = $order?->user;
        if ($user) {
            $this->notifyUser(
                $user,
                'Order delivered',
                "Your order {$order->reference} has been delivered. Thank you for shopping with Sara Commercial.",
                [
                    'event' => 'order_delivered',
                    'order_id' => $order->id,
                    'reference' => $order->reference,
                ]
            );
        }
    }

    public function adminNewOrder(?Order $order): void
    {
        $this->log('admin_new_order', $order);
    }

    public function userRegistered(User $user): void
    {
        $this->notifyUser(
            $user,
            'Welcome to Sara Commercial',
            "Hi {$user->name}, your account has been created successfully.",
            ['event' => 'user_registered', 'user_id' => $user->id]
        );
    }

    public function userLoggedIn(User $user): void
    {
        $this->notifyUser(
            $user,
            'New login detected',
            "Hi {$user->name}, your account was just logged in.",
            ['event' => 'user_logged_in', 'user_id' => $user->id]
        );
    }

    public function productAddedToCart(User $user, string $productName, int $quantity): void
    {
        $this->notifyUser(
            $user,
            'Product added to cart',
            "Added to cart: {$productName} (Qty {$quantity}).",
            [
                'event' => 'product_added_to_cart',
                'user_id' => $user->id,
                'product' => $productName,
                'quantity' => $quantity,
            ]
        );
    }

    public function otpSent(string $phone, string $channel, string $code): void
    {
        $message = "Your Sara Commercial OTP is {$code}. Valid for 10 minutes.";
        $ctx = ['event' => 'otp_sent', 'channel' => $channel];

        if (app()->environment('local')) {
            Log::info('OTP local preview', [
                'phone' => $phone,
                'channel' => $channel,
                'code' => $code,
            ]);
        }

        SendUserMessageJob::dispatch($phone, $message, $channel, $ctx);
        Log::info('OTP notification queued', array_merge($ctx, ['phone' => $phone]));
    }

    private function log(string $event, ?Order $order): void
    {
        Log::info('Notification: '.$event, [
            'order_id' => $order?->id,
            'reference' => $order?->reference,
            'user_id' => $order?->user_id,
        ]);
    }

    public function notifyUser(User $user, string $subject, string $body, array $context = []): void
    {
        $payload = array_merge([
            'user_id' => $user->id,
            'subject' => $subject,
            'body' => $body,
        ], $context);

        Log::info('Notification to user', $payload);
        $this->sendEmail($user, $subject, $body, $payload);
        $this->sendSms($user, $body, $payload);
        $this->sendWhatsapp($user, $body, $payload);
    }

    private function sendEmail(User $user, string $subject, string $body, array $context): void
    {
        if (! $user->email) {
            return;
        }

        try {
            Mail::raw($body, function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });

            Log::info('Email notification sent', array_merge($context, ['email' => $user->email]));
        } catch (Throwable $e) {
            Log::warning('Email notification failed', array_merge($context, [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]));
        }
    }

    private function sendSms(User $user, string $body, array $context): void
    {
        if (! $user->phone) {
            return;
        }

        SendUserMessageJob::dispatch($user->phone, $body, 'sms', $context);

        Log::info('SMS notification queued', array_merge($context, [
            'phone' => $user->phone,
        ]));
    }

    private function sendWhatsapp(User $user, string $body, array $context): void
    {
        if (! $user->phone) {
            return;
        }

        SendUserMessageJob::dispatch($user->phone, $body, 'whatsapp', $context);

        Log::info('WhatsApp notification queued', array_merge($context, [
            'phone' => $user->phone,
        ]));
    }
}
