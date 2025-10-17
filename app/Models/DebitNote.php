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
        'status',       // unused | partially-used | used
        'reason',
        'issued_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'issued_date' => 'date',
    ];

    /** ───────────────────────────────
     *  Relationships
     *  ─────────────────────────────── */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** ───────────────────────────────
     *  Accessors
     *  ─────────────────────────────── */
    public function getRemainingAttribute(): float
    {
        return round($this->amount - $this->used_amount, 2);
    }

    /** ───────────────────────────────
     *  Scopes
     *  ─────────────────────────────── */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'used');
    }

    /** ───────────────────────────────
     *  Boot (auto-generate debit_note_id)
     *  ─────────────────────────────── */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->debit_note_id)) {
                $last = DebitNote::orderBy('id', 'desc')->first();
                $next = $last ? intval(substr($last->debit_note_id, 3)) + 1 : 1;
                $model->debit_note_id = 'DN-' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }

            if (empty($model->used_amount)) {
                $model->used_amount = 0;
            }

            if (empty($model->status)) {
                $model->status = 'unused';
            }

            if (empty($model->issued_date)) {
                $model->issued_date = now();
            }
        });
    }
}
