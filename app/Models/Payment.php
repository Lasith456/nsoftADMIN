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
    ];

    protected function casts(): array
    {
        return [
            'payment_date'         => 'date',
            'amount'               => 'decimal:2',
            'cheque_date'          => 'date',
            'cheque_received_date' => 'date',
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
