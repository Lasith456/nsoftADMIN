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
        // Decide prefix based on type
        $prefix = match ($invoice->invoiceable_type) {
            \App\Models\Customer::class => 'INV_CUSTOMER',
            \App\Models\Supplier::class => 'INV_SUPPLIER',
            \App\Models\Agent::class    => 'INV_AGENT',
            default                     => 'INV',
        };

        // Find latest invoice of the same type
        $latest = static::where('invoiceable_type', $invoice->invoiceable_type)
                        ->latest('id')
                        ->first();

        if (!$latest) {
            $number = 1;
        } else {
            // Extract the number part safely
            $lastId = $latest->invoice_id;
            preg_match('/(\d+)$/', $lastId, $matches);
            $lastNumber = $matches[1] ?? 0;
            $number = (int) $lastNumber + 1;
        }

        // Generate invoice_id with type prefix
        $invoice->invoice_id = $prefix . '-' . str_pad($number, 4, "0", STR_PAD_LEFT);
    });
}

}
