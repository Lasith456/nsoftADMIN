<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DebitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'debit_note_id',
        'amount',
        'used_amount',
        'status',
        'reason',
        'issued_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'issued_date' => 'date',
    ];

    // Relationship
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Accessor to get remaining balance
    public function getRemainingAttribute()
    {
        return $this->amount - $this->used_amount;
    }
}
