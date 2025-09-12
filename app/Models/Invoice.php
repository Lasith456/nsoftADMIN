<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoiceable_id',
        'invoiceable_type',
        'due_date',
        'sub_total',      // Add this
        'vat_percentage', // Add this
        'vat_amount',   
        'total_amount',
        'amount_paid',
        'status',
        'notes',
        'is_vat_invoice'
    ];

protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'sub_total' => 'decimal:2',
            'vat_percentage' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'is_vat_invoice' => 'boolean',
        ];
    }


    /**
     * Get the parent invoiceable model (customer, supplier, or agent).
     */
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all of the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all of the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
