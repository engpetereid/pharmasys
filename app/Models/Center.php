<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function pharmacists()
    {
        return $this->hasMany(Pharmacist::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'center_zone');
    }
}
