<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class GrnPo extends Model
{
    use HasFactory;

    protected $fillable = [
        'grnpo_id', 'supplier_id', 'delivery_date', 'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GrnPoItem::class, 'grnpo_id');
    }

    // Boot method for ID generation + logging
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($grnpo) {
            $latest = static::latest('id')->first();
            $next = $latest ? ((int) substr($latest->grnpo_id, 6)) + 1 : 0;
            $grnpo->grnpo_id = 'GRNPO-' . str_pad($next, 4, '0', STR_PAD_LEFT);

            Log::info('Creating new GRN PO record', [
                'latest_id' => $latest?->id,
                'next_number' => $next,
                'generated_grnpo_id' => $grnpo->grnpo_id,
            ]);
        });

        static::created(function ($grnpo) {
            Log::info('GRN PO successfully created', [
                'id' => $grnpo->id,
                'grnpo_id' => $grnpo->grnpo_id,
                'supplier_id' => $grnpo->supplier_id,
                'delivery_date' => $grnpo->delivery_date,
            ]);
        });
    }
}
