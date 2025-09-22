<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'product_id',
        'unit_type',
        'stock_type', 
        'quantity_received',
        'units_per_case',
        'cost_price',
        'selling_price',
        'discount',
        'serial',
        'serial',
        'is_free_issue',   
        'free_issue_qty',    
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Get the GRN that owns the item.
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    /**
     * Get the product associated with the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

