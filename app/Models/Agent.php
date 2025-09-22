<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; 
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'name',
        'product_id',
        'contact_no',
        'address',
        'email',
        'credit_period',
        'credit_limit',
        'price_per_case',
        'units_per_case',
        'unit_of_measure',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
            'price_per_case' => 'decimal:2',
        ];
    }
    
   public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'agent_product_pivot')
                    ->withPivot('price_per_case') 
                    ->withTimestamps();
    }

    /**
     * Get all of the agent's invoices.
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    /**
     * Get all of the delivery note items for the agent.
     */
    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
               protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new agent is being created.
        static::creating(function ($agent) {
            // Find the latest agent to determine the next ID.
            $latestAgent = static::latest('id')->first();

            if (!$latestAgent) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last agent_id (e.g., from "AGENT-0009"), and increment it.
                $lastNumber = (int) substr($latestAgent->agent_id, 6);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new agent's agent_id.
            $agent->agent_id = 'AGENT-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
    
}

