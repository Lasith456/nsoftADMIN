<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany; // Import this

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'title',
        'supplier_name',
        'company_name',
        'display_name',
        'nic',
        'primary_address',
        'supplier_mobile',
        'office_no',
        'fax',
        'work_phone',
        'email',
        'company_address',
        'credit_limit',
        'credit_period',
        'remark',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Get all of the supplier's invoices.
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }
        public function grns(): HasMany
    {
        return $this->hasMany(Grn::class);
    }
    protected static function boot()
    {
        parent::boot();

        // This event is triggered automatically when a new supplier is being created.
        static::creating(function ($supplier) {
            // Find the latest supplier to determine the next ID.
            $latestSupplier = static::latest('id')->first();

            if (!$latestSupplier) {
                // If the table is empty, start with number 1.
                $number = 1;
            } else {
                // Get the number from the last supplier_id (e.g., from "SUPPLIER-0009"), and increment it.
                $lastNumber = (int) substr($latestSupplier->supplier_id, 9);
                $number = $lastNumber + 1;
            }

            // Format the number with 4 leading zeros (e.g., 1 becomes "0001")
            // and assign it to the new supplier's supplier_id.
            $supplier->supplier_id = 'SUPPLIER-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}

