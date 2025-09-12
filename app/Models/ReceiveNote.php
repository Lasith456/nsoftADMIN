<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

        // This event is triggered automatically when a new receive note is being created.
        static::creating(function ($receiveNote) {
            // Find the latest receive note to determine the next ID.
            $latestReceiveNote = static::latest('id')->first();

            if (!$latestReceiveNote) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last receive_note_id (e.g., from "RN-0009"), and increment it.
                $lastNumber = (int) substr($latestReceiveNote->receive_note_id, 6);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new receive note's receive_note_id.
            $receiveNote->receive_note_id = 'RN-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}
