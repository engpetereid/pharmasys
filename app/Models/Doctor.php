<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctors';
    protected $fillable = ['name', 'address' , 'phone' ,'speciality' ,'center_id'];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'doctor_invoice');
    }
//    public function invoices()
//    {
//        return $this->hasMany(Invoice::class);
//    }


    public function deals()
    {
        return $this->hasMany(DoctorDeal::class);
    }

    public function getPrepaidRiskAttribute()
    {
        return $this->deals()
            ->where('is_prepaid', true)
            ->where('is_paid', false)
            ->sum('commission_amount');
    }


}
