<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $fillable = [
        'device_id', 'issued_by_type', 'issued_by_id',
        'command',   // lock | unlock | kiosk | wipe | message | update_config
        'payload',   // JSON extra data
        'status',    // pending | delivered | executed | failed
        'delivered_at', 'executed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'delivered_at' => 'datetime',
        'executed_at'  => 'datetime',
    ];

    public function device() { return $this->belongsTo(Device::class); }
}
