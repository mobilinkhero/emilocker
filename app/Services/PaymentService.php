<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Payment;
use App\Models\EmiPlan;

class PaymentService
{
    public function __construct(private DeviceCommandService $commandService) {}

    public function processPayment(Device $device, array $data): array
    {
        $plan = $device->emiPlan;

        if (! $plan || $plan->status !== 'active') {
            return ['error' => 'No active EMI plan found.'];
        }

        $lateFee   = $plan->calculateLateFee();
        $totalPaid = $data['amount'];
        $isPartial = $data['is_partial'] ?? false;

        $payment = Payment::create([
            'device_id'      => $device->id,
            'emi_plan_id'    => $plan->id,
            'customer_id'    => $device->customer_id,
            'amount'         => $data['amount'],
            'late_fee'       => $lateFee,
            'total_paid'     => $totalPaid,
            'method'         => $data['method'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'status'         => 'completed',
            'is_partial'     => $isPartial,
            'partial_unlock_hours' => $isPartial ? 24 : null,
            'paid_at'        => now(),
            'due_date'       => $plan->next_due_date,
        ]);

        // Update plan
        $plan->increment('paid_installments');
        $plan->decrement('remaining_amount', $data['amount']);

        $frequencyDays = $plan->frequency_days;
        $plan->update(['next_due_date' => now()->addDays($frequencyDays)]);

        // Check if plan completed
        if ($plan->paid_installments >= $plan->total_installments || $plan->remaining_amount <= 0) {
            $plan->update(['status' => 'completed', 'completed_at' => now()]);
            $device->update(['status' => 'active', 'lock_reason' => null]);
            $this->commandService->sendCommand($device, 'unlock', ['reason' => 'emi_completed']);

            return ['message' => 'EMI fully paid. Device permanently unlocked.', 'payment' => $payment];
        }

        // Partial payment: temporary unlock
        if ($isPartial) {
            $this->commandService->sendCommand($device, 'unlock', [
                'reason'       => 'partial_payment',
                'unlock_hours' => 24,
            ]);
            return ['message' => 'Partial payment recorded. Device unlocked for 24 hours.', 'payment' => $payment];
        }

        // Full installment paid: unlock
        $device->update(['status' => 'active', 'lock_reason' => null]);
        $this->commandService->sendCommand($device, 'unlock', ['reason' => 'installment_paid']);

        return ['message' => 'Payment recorded. Device unlocked.', 'payment' => $payment];
    }
}
