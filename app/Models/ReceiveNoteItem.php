<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiveNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receive_note_id',
        'product_id',
        'quantity_expected',
        'quantity_received',
        'discrepancy_reason',
    ];

    /**
     * Get the receive note that owns the item.
     */
    public function receiveNote(): BelongsTo
    {
        return $this->belongsTo(ReceiveNote::class);
    }

    /**
     * Get the product associated with the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
