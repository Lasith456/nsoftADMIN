<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Add this line
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;
 protected $appends = ['receive_notes'];
    protected $fillable = [
        'customer_id',
        'title',
        'customer_name',
        'company_name',
        'display_name',
        'nic',
        'primary_address',
        'customer_mobile',
        'customer_phone',
        'work_phone',
        'customer_email',
        'company_address',
        'credit_limit',
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

    /**
     * Get all of the customer's purchase orders.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get all the receive notes associated with a customer through their orders.
     * This is an accessor, not a direct relationship that can be queried with 'whereHas'.
     * Usage: $customer->receive_notes
     */
    public function getReceiveNotesAttribute()
    {
        return $this->purchaseOrders->flatMap(function ($po) {
            return $po->deliveryNotes->flatMap(function ($dn) {
                return $dn->receiveNotes;
            });
        })->unique('id');
    }

    /**
     * Get all of the customer's invoices.
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            $latestCustomer = static::latest('id')->first();
            $number = $latestCustomer ? (int) substr($latestCustomer->customer_id, 5) + 1 : 1;
            $customer->customer_id = 'CUST-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}