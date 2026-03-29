<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representative extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'representative_id');
    }

    public function salesZones()
    {
        return $this->hasMany(Zone::class, 'sales_representative_id');
    }

    public function medicalZones()
    {
        return $this->hasMany(Zone::class, 'medical_representative_id');
    }
}
