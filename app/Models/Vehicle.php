<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_no',
        'vehicle_id',
        'title',
        'driver_name',
        'driver_nic',
        'driver_address',
        'driver_mobile',
        'assistant_name',
        'assistant_mobile',
        'assistant_nic',
        'assistant_address',
        'remark',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}