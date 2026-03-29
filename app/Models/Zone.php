<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'province_id', 'line',
        'sales_representative_id', 'medical_representative_id',
        'warehouse_id'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function centers()
    {
        return $this->belongsToMany(Center::class, 'center_zone');
    }

    public function salesRepresentative()
    {
        return $this->belongsTo(Representative::class, 'sales_representative_id');
    }

    public function medicalRepresentative()
    {
        return $this->belongsTo(Representative::class, 'medical_representative_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function expenses()
    {
        return $this->hasMany(ZoneExpense::class)->latest('expense_date');
    }
}
