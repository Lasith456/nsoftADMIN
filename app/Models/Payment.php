<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'batch_id',
        'bank_id',
        'cheque_number',
        'cheque_date',
        'cheque_received_date',
        'stamp_fee',       
        'surcharge_fee',   
        'used_debit',
    ];

    protected function casts(): array
    {
        return [
            'payment_date'         => 'date',
            'amount'               => 'decimal:2',
            'cheque_date'          => 'date',
            'cheque_received_date' => 'date',
             'stamp_fee'            => 'decimal:2',
            'surcharge_fee'        => 'decimal:2',
            'used_debit'           => 'decimal:2',

        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
