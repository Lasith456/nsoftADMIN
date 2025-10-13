<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 
        'customer_id', 
        'agent_id', 
        'reason', 
        'return_date', 
        'receive_note_id',
        'status', 
        'product_id', 
        'quantity', 
        'created_by',
        'session_token'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->return_note_id)) {
                // Get the last numeric part of the last return_note_id
                $last = self::orderBy('id', 'desc')->first();

                if ($last && preg_match('/RT-(\d+)/', $last->return_note_id, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                } else {
                    $nextNumber = 1;
                }

                // Format: RT-0001, RT-0002, etc.
                $model->return_note_id = 'RT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }


    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function receiveNote()
    {
        return $this->belongsTo(\App\Models\ReceiveNote::class, 'receive_note_id');
    }

}
