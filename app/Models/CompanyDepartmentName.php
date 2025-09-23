<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDepartmentName extends Model
{
    protected $fillable = ['company_id', 'department_id', 'appear_name'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
