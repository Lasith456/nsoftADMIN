<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_id',
        'customer_id',
        'delivery_date',
        'vehicle_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class, 'delivery_note_purchase_order');
    }
    
    protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new purchase order is being created.
        static::creating(function ($po) {
            // Find the purchase order with the highest number in its po_id.
            // We order by the numeric part of the po_id to avoid string sorting issues (e.g., where 'PO-9' is greater than 'PO-10').
            $latestPo = static::orderByRaw('CAST(SUBSTRING(po_id, 4) AS UNSIGNED) DESC')->first();

            if (!$latestPo) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last po_id and increment it.
                $lastNumber = (int) substr($latestPo->po_id, 3);
                $number = $lastNumber + 1;
            }

            // Format the new number and assign it to the new purchase order's po_id.
            $po->po_id = 'PO-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}

