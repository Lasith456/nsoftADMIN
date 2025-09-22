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
        'sub_total',
        'vat_percentage',
        'vat_amount',
        'total_amount',
        'amount_paid',
        'status',
        'notes',
        'is_vat_invoice',
    ];

    protected function casts(): array
    {
        return [
            'due_date'       => 'date',
            'sub_total'      => 'decimal:2',
            'vat_percentage' => 'decimal:2',
            'vat_amount'     => 'decimal:2',
            'total_amount'   => 'decimal:2',
            'amount_paid'    => 'decimal:2',
            'is_vat_invoice' => 'boolean',
        ];
    }

    /**
     * Polymorphic relation: the invoice belongs to a Customer, Supplier, or Agent.
     */
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Items belonging to this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments recorded against this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Auto-generate invoice_id like "INV-0001" on create.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $latest = static::latest('id')->first();

            if (!$latest) {
                $number = 1;
            } else {
                // Extract numeric part from last invoice_id (after "INV-")
                $lastNumber = (int) substr($latest->invoice_id, 4);
                $number = $lastNumber + 1;
            }

            // Format with 4 digits padded with zeros
            $invoice->invoice_id = 'INV-' . str_pad($number, 4, "0", STR_PAD_LEFT);
        });
    }
}
