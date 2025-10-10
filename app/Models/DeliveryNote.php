<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'vehicle_id',
        'delivery_date',
        'status',
        'driver_name',
        'driver_mobile',
        'assistant_name', 
        'assistant_mobile',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

    // -------------------------------
    // 🔗 RELATIONSHIPS
    // -------------------------------

    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'delivery_note_purchase_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function receiveNotes(): BelongsToMany
    {
        return $this->belongsToMany(ReceiveNote::class, 'delivery_note_receive_note');
    }

    /**
     * ✅ Fetch the customer through the first linked purchase order
     */
    public function getCustomerAttribute()
    {
        return $this->purchaseOrders->first()?->customer;
    }

    /**
     * ✅ Fetch the company through the first linked purchase order's customer
     */
    public function getCompanyAttribute()
    {
        return $this->purchaseOrders->first()?->customer?->company;
    }

    // -------------------------------
    // 🔢 AUTO-GENERATE DELIVERY NOTE ID
    // -------------------------------
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryNote) {
            $latestDeliveryNote = static::lockForUpdate()->latest('id')->first();
            $nextNumber = 1;

            if ($latestDeliveryNote) {
                $lastIdNumber = static::lockForUpdate()
                    ->max(DB::raw("CAST(SUBSTRING(delivery_note_id, 4) AS UNSIGNED)"));
                $nextNumber = $lastIdNumber + 1;
            }

            $deliveryNote->delivery_note_id = 'DN-' . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);
        });
    }
}
