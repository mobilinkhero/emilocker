<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'phone', 'cnic', 'address', 'password', 'is_active'
    ];

    protected $hidden = ['password', 'cnic'];

    public function devices()  { return $this->hasMany(Device::class); }
    public function payments() { return $this->hasManyThrough(Payment::class, Device::class); }
}
