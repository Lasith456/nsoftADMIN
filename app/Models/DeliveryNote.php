<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'vehicle_id',
        'delivery_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
        ];
    }

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

    /**
     * The receive notes that this delivery note belongs to.
     */
    public function receiveNotes(): BelongsToMany
    {
        return $this->belongsToMany(ReceiveNote::class, 'delivery_note_receive_note');
    }
    protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new delivery note is being created.
        static::creating(function ($deliveryNote) {
            // Find the latest delivery note to determine the next ID.
            $latestDeliveryNote = static::latest('id')->first();

            if (!$latestDeliveryNote) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last delivery_note_id (e.g., from "DN-0009"), and increment it.
                $lastNumber = (int) substr($latestDeliveryNote->delivery_note_id, 6);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new delivery note's delivery_note_id.
            $deliveryNote->delivery_note_id = 'DN-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}


