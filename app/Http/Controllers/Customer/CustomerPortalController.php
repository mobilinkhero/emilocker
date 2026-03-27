<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmiPlan;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $devices  = $customer->devices()->with('emiPlan')->get();

        return view('customer.dashboard', compact('customer', 'devices'));
    }

    public function payments()
    {
        $customer = Auth::guard('customer')->user();
        $payments = $customer->payments()->with('device')->latest()->paginate(15);

        return view('customer.payments', compact('payments'));
    }

    public function payNow(int $planId)
    {
        $customer = Auth::guard('customer')->user();
        $plan     = EmiPlan::whereHas('device', fn($q) => $q->where('customer_id', $customer->id))
            ->with(['device', 'payments'])
            ->findOrFail($planId);

        return view('customer.pay', compact('plan'));
    }

    public function processPayment(Request $request, int $planId)
    {
        $customer = Auth::guard('customer')->user();
        $plan     = EmiPlan::whereHas('device', fn($q) => $q->where('customer_id', $customer->id))
            ->findOrFail($planId);

        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'method'         => 'required|in:jazzcash,easypaisa,card,cash,bank',
            'transaction_id' => 'nullable|string',
        ]);

        $result = $this->paymentService->processPayment($plan->device, $data);

        return redirect()->route('customer.payments')->with('success', $result['message']);
    }
}
