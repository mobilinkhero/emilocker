<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id', 'event_type',
        // sim_change | root_detected | factory_reset_attempt |
        // offline_lock | payment_received | command_executed | location_update
        'payload', 'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    public function device() { return $this->belongsTo(Device::class); }
}
