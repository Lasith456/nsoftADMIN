<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'stock_type',
        'reason',
    ];

    /**
     * Get the product associated with the wastage log.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
