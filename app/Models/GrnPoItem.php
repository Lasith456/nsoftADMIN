<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnPoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grnpo_id', 'department_id', 'product_id', 'quantity',
    ];

    public function grnpo()
    {
        return $this->belongsTo(GrnPo::class, 'grnpo_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
