<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProductPrice extends Model
{
    protected $fillable = ['product_id', 'company_id', 'cost_price', 'selling_price'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
