<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grn extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'delivery_date',
        'supplier_id',
        'invoice_number',
        'status', 
        'total_amount',
        'total_discount',
        'net_amount',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'total_amount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the supplier associated with the GRN.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all of the items for the GRN.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class);
    }
    protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new grn is being created.
        static::creating(function ($grn) {
            // Find the latest grn to determine the next ID.
            $latestGrn = static::latest('id')->first();

            if (!$latestGrn) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last grn_id (e.g., from "GRN-0009"), and increment it.
                $lastNumber = (int) substr($latestGrn->grn_id, 6);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new grn's grn_id.
            $grn->grn_id = 'GRN-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
    
}

