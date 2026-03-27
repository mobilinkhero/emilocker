<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'imei', 'imei2', 'model', 'brand', 'android_version',
        'api_key',
        'fcm_token', 'sim_serial', 'phone_number',
        'status',        // active | locked | kiosk | wiped
        'lock_reason',
        'is_online', 'last_seen_at',
        'battery_level', 'storage_used',
        'latitude', 'longitude',
        'is_rooted', 'is_tampered',
        'registered_at',
    ];

    protected $casts = [
        'is_online'    => 'boolean',
        'is_rooted'    => 'boolean',
        'is_tampered'  => 'boolean',
        'last_seen_at' => 'datetime',
        'registered_at'=> 'datetime',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function emiPlan()  { return $this->hasOne(EmiPlan::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function commands() { return $this->hasMany(DeviceCommand::class); }
    public function events()   { return $this->hasMany(DeviceEvent::class); }

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'kiosk']);
    }
}
