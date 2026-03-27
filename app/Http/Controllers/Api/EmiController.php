<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\EmiPlan;
use App\Models\Payment;
use App\Services\DeviceCommandService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class EmiController extends Controller
{
    public function __construct(
        private DeviceCommandService $commandService,
        private PaymentService $paymentService,
    ) {}

    /**
     * Assign EMI plan to a device (retailer action)
     */
    public function createPlan(Request $request, string $imei)
    {
        $device = Device::where('imei', $imei)->firstOrFail();

        $data = $request->validate([
            'total_amount'        => 'required|numeric|min:1',
            'down_payment'        => 'required|numeric|min:0',
            'installment_amount'  => 'required|numeric|min:1',
            'frequency'           => 'required|in:weekly,monthly,custom',
            'frequency_days'      => 'required_if:frequency,custom|integer|min:1',
            'total_installments'  => 'required|integer|min:1',
            'late_fee_per_day'    => 'nullable|numeric|default:0',
            'grace_period_days'   => 'nullable|integer|default:3',
            'start_date'          => 'required|date',
        ]);

        $frequencyDays = match($data['frequency']) {
            'weekly'  => 7,
            'monthly' => 30,
            'custom'  => $data['frequency_days'],
        };

        $plan = EmiPlan::create([
            ...$data,
            'device_id'          => $device->id,
            'remaining_amount'   => $data['total_amount'] - $data['down_payment'],
            'paid_installments'  => 0,
            'frequency_days'     => $frequencyDays,
            'next_due_date'      => now()->parse($data['start_date'])->addDays($frequencyDays),
            'status'             => 'active',
        ]);

        // Unlock device once plan is assigned
        $device->update(['status' => 'active']);
        $this->commandService->sendCommand($device, 'unlock', ['reason' => 'emi_plan_assigned']);

        return response()->json(['plan' => $plan], 201);
    }

    public function schedule(string $deviceId)
    {
        $device = Device::with(['emiPlan.payments'])->findOrFail($deviceId);
        $plan   = $device->emiPlan;

        return response()->json([
            'plan'      => $plan,
            'late_fee'  => $plan?->calculateLateFee(),
            'payments'  => $plan?->payments,
        ]);
    }

    /**
     * Record a payment and auto-unlock if applicable
     */
    public function recordPayment(Request $request, string $deviceId)
    {
        $device = Device::with('emiPlan')->findOrFail($deviceId);

        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:jazzcash,easypaisa,card,cash,bank',
            'transaction_id' => 'nullable|string',
            'is_partial'     => 'boolean',
        ]);

        $result = $this->paymentService->processPayment($device, $data);

        return response()->json($result);
    }
}
