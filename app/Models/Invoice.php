<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_code',
        'invoice_type',
        'invoiceable_id',
        'invoiceable_type',
        'customer_id',
        'company_id',
        'invoice_date',
        'po_start_date',
        'po_end_date',
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
            'invoice_date'     => 'date',
            'po_start_date'    => 'date',
            'po_end_date'      => 'date',
            'sub_total'        => 'decimal:2',
            'vat_percentage'   => 'decimal:2',
            'vat_amount'       => 'decimal:2',
            'total_amount'     => 'decimal:2',
            'amount_paid'      => 'decimal:2',
            'is_vat_invoice'   => 'boolean',
        ];
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function receiveNotes()
    {
        return $this->belongsToMany(ReceiveNote::class, 'invoice_receive_note');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * ✅ Auto calculate totals
     */
    public function recalculateTotals()
    {
        $subTotal = $this->items()->sum(DB::raw('quantity * unit_price'));
        $vatTotal = $this->items()->sum('vat_amount');

        $this->update([
            'sub_total'    => round($subTotal, 2),
            'vat_amount'   => round($vatTotal, 2),
            'total_amount' => round($subTotal + $vatTotal, 2),
        ]);
    }
    // App\Models\Invoice.php
protected static function boot()
{
    parent::boot();

    static::creating(function ($invoice) {
        // Only generate if not set by controller
        if (! $invoice->invoice_id) {
            $prefix = match ($invoice->invoiceable_type) {
                \App\Models\Customer::class => 'INV_CUS',
                \App\Models\Supplier::class => 'INV_SUPP',
                \App\Models\Agent::class    => 'INV_AGEN',
                default                     => 'INV',
            };

            $latest = static::where('invoiceable_type', $invoice->invoiceable_type)
                            ->latest('id')
                            ->first();

            $number = 1;
            if ($latest && preg_match('/(\d+)$/', $latest->invoice_id, $m)) {
                $number = ((int)$m[1]) + 1;
            }

            $invoice->invoice_id = $prefix.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    });
}

}
