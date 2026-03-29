<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Warehouse::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Warehouse::class, 'parent_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function drugs()
    {
        return $this->belongsToMany(Drug::class, 'drug_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }
    public function distributionAreas()
    {

        return $this->hasMany(Zone::class);
    }
}
