<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'quantity_requested',
        'quantity_from_stock',
        'agent_id',
        'quantity_from_agent',
        'product_name',
        'agent_invoiced',
    ];
    protected function casts(): array
    {
        return [
            'agent_invoiced' => 'boolean', 
        ];
    }
    /**
     * Get the delivery note that owns the item.
     */
    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    /**
     * Get the product associated with the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the agent assigned to fulfill the item's shortage.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
    public function isAgentProduct(): bool
{
    return !is_null($this->agent_id);
}

}
