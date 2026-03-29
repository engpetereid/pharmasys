<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'phone', 'address', 'center_id'];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function deals()
    {
        return $this->belongsToMany(DoctorDeal::class, 'deal_pharmacist');
    }
}
