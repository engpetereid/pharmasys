<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'invoice_details';

    protected $fillable = [
        'invoice_id',
        'drug_id',
        'quantity',
        'unit_price',
        'pharmacist_discount_percentage',
        'row_total'
    ];
    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }

    public function drug(){
        return $this->belongsTo(Drug::class);
    }

}
