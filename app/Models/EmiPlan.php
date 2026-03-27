<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmiPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id', 'total_amount', 'down_payment',
        'installment_amount', 'frequency',   // weekly | monthly | custom
        'frequency_days',                    // used when frequency = custom
        'total_installments', 'paid_installments',
        'remaining_amount', 'late_fee_per_day',
        'grace_period_days',
        'start_date', 'next_due_date', 'completed_at',
        'status',   // active | completed | defaulted
    ];

    protected $casts = [
        'start_date'    => 'date',
        'next_due_date' => 'date',
        'completed_at'  => 'datetime',
    ];

    public function device()   { return $this->belongsTo(Device::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function isOverdue(): bool
    {
        return $this->next_due_date && $this->next_due_date->isPast()
            && $this->status === 'active';
    }

    public function calculateLateFee(): float
    {
        if (! $this->isOverdue()) return 0;
        $days = now()->diffInDays($this->next_due_date);
        return max(0, $days - $this->grace_period_days) * $this->late_fee_per_day;
    }
}
