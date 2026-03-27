<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id', 'emi_plan_id', 'customer_id',
        'amount', 'late_fee', 'total_paid',
        'method',           // jazzcash | easypaisa | card | cash | bank
        'transaction_id', 'gateway_response',
        'status',           // pending | completed | failed | partial
        'is_partial',
        'partial_unlock_hours',   // unlock duration for partial payment
        'paid_at', 'due_date',
        'notes',
    ];

    protected $casts = [
        'paid_at'          => 'datetime',
        'due_date'         => 'date',
        'is_partial'       => 'boolean',
        'gateway_response' => 'array',
    ];

    public function device()  { return $this->belongsTo(Device::class); }
    public function emiPlan() { return $this->belongsTo(EmiPlan::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
}
