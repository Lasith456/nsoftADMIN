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
        'driver_mobile'
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
    
    /**
     * THE FIX IS HERE: This logic is now more robust to prevent duplicate IDs.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliveryNote) {
            // Lock the table to prevent race conditions where two processes might get the same last ID.
            $latestDeliveryNote = static::lockForUpdate()->latest('id')->first();
            $nextNumber = 1;

            if ($latestDeliveryNote) {
                // Find the highest numeric part of any existing delivery_note_id.
                // This is reliable even if records are deleted out of order.
                $lastIdNumber = static::lockForUpdate()->max(DB::raw("CAST(SUBSTRING(delivery_note_id, 4) AS UNSIGNED)"));
                $nextNumber = $lastIdNumber + 1;
            }
            
            // Format and assign the new, unique ID.
            $deliveryNote->delivery_note_id = 'DN-' . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);
        });
    }
}
