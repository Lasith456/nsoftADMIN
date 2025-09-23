<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['company_name'];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    public function productPrices()
{
    return $this->hasMany(CompanyProductPrice::class);
}

}