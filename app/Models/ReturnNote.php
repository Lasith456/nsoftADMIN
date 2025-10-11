<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_note_id',
        'company_id',
        'customer_id',
        'return_date',
         'agent_id', 
        'reason',
        'status',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->return_note_id)) {
                $model->return_note_id = 'RN-' . strtoupper(Str::random(6));
            }
        });
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
