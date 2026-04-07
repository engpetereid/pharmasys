<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. إنشاء جدول الدفعات
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 2. هجرة البيانات (Data Migration):
        // نقل الدفعات المسجلة سابقاً في الفواتير إلى الجدول الجديد للحفاظ على دقة حساباتك القديمة
        DB::table('invoices')->where('paid_amount', '>', 0)->orderBy('id')->chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                DB::table('invoice_payments')->insert([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->paid_amount,
                    'payment_date' => $invoice->invoice_date, // اعتبار تاريخ الدفعة القديمة هو تاريخ الفاتورة
                    'notes' => 'رصيد مسدد مسبقاً (تم نقله تلقائياً)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_payments');
    }
};
