<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'target_amount',
        'achieved_amount',
        'commission_amount',
        'commission_percentage',
        'is_prepaid',
        'is_paid',
        'start_date',
        'end_date',
        'status',
        'paid_amount',
        'is_active',
        'is_archived'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
    ];
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function pharmacists()
    {
        return $this->belongsToMany(Pharmacist::class, 'deal_pharmacist');
    }

    public function drugs()
    {
        return $this->belongsToMany(Drug::class, 'deal_drug');
    }

    public function getRemainingTargetAttribute()
    {
        return max(0, $this->target_amount - $this->achieved_amount);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'doctor_deal_invoices')
                ->withPivot('contribution_amount')
                ->withTimestamps();
    }
}
