<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'appear_name',
        'department_id',
        'sub_department_id',
        'supplier_id', 
        'is_active',
        'is_vat',
        'units_per_case',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'reorder_qty',
        'clear_stock_quantity',      
        'non_clear_stock_quantity', 
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_vat' => 'boolean',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    /**
     * Accessor to get the total stock quantity.
     */
    public function getTotalStockAttribute()
    {
        return $this->clear_stock_quantity + $this->non_clear_stock_quantity;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
       protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new product is being created.
        static::creating(function ($product) {
            // Find the latest product to determine the next ID.
            $latestProduct = static::latest('id')->first();

            if (!$latestProduct) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last product_id (e.g., from "PROD-0009"), and increment it.
                $lastNumber = (int) substr($latestProduct->product_id, 5);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new product's product_id.
            $product->product_id = 'PROD-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'agent_product_pivot')
                    ->withPivot('price_per_case')
                    ->withTimestamps();
    }
}

