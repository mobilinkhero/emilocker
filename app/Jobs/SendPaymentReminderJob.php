<?php

namespace App\Jobs;

use App\Models\EmiPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends payment reminder via SMS (and optionally WhatsApp) 2 days before due date.
 * Scheduled daily in Kernel.php.
 */
class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find plans due in exactly 2 days
        EmiPlan::with(['device.customer'])
            ->where('status', 'active')
            ->whereDate('next_due_date', now()->addDays(2)->toDateString())
            ->chunk(50, function ($plans) {
                foreach ($plans as $plan) {
                    $customer = $plan->device?->customer;
                    if (! $customer?->phone) continue;

                    $message = "Dear {$customer->name}, your EMI installment of PKR "
                        . number_format($plan->installment_amount)
                        . " is due on {$plan->next_due_date->format('d M Y')}. "
                        . "Please pay on time to avoid device lock. NoxLock.";

                    $this->sendSms($customer->phone, $message);
                }
            });
    }

    private function sendSms(string $phone, string $message): void
    {
        // Using Twilio — swap with any SMS gateway (Jazz, Telenor, etc.)
        try {
            Http::withBasicAuth(
                config('services.twilio.sid'),
                config('services.twilio.token')
            )->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.twilio.sid') . "/Messages.json", [
                'From' => config('services.twilio.from'),
                'To'   => '+92' . ltrim($phone, '0'),
                'Body' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("SMS failed to {$phone}: " . $e->getMessage());
        }
    }
}
