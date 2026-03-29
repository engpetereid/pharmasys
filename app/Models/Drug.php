<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $table = 'drugs';
    protected $fillable = ['name', 'price', 'line'];

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'drug_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function deals()
    {
        return $this->belongsToMany(DoctorDeal::class, 'deal_drug');
    }

    public function stockIn($warehouseId)
    {
        $record = $this->warehouses()->where('warehouse_id', $warehouseId)->first();
        return $record ? $record->pivot->quantity : 0;
    }
}
