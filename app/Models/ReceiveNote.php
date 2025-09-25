<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
class ReceiveNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'receive_note_id',
        'received_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
        ];
    }

    /**
     * The delivery notes that belong to this receive note.
     */
    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class, 'delivery_note_receive_note');
    }

    /**
     * Get all of the items for the receive note.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReceiveNoteItem::class);
    }
  protected static function boot()
    {
        parent::boot();

        static::creating(function ($receiveNote) {
            // Lock the table to prevent race conditions where two processes might get the same last ID.
            $latestReceiveNote = static::lockForUpdate()->latest('id')->first();
            $nextNumber = 1;

            if ($latestReceiveNote) {
                // Find the highest numeric part of any existing receive_note_id.
                // This is reliable even if records are deleted out of order.
                $lastIdNumber = static::lockForUpdate()->max(DB::raw("CAST(SUBSTRING(receive_note_id, 4) AS UNSIGNED)"));
                $nextNumber = $lastIdNumber + 1;
            }
            
            // Format and assign the new, unique ID.
            $receiveNote->receive_note_id = 'RN-' . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);
        });
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_receive_note');
    }
}
