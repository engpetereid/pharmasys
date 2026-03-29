<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. إنشاء الجدول الوسيط
        Schema::create('doctor_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. نقل البيانات القديمة (إذا كان هناك فواتير مسجلة مسبقاً بطبيب واحد)
        DB::table('invoices')->whereNotNull('doctor_id')->orderBy('id')->chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                DB::table('doctor_invoice')->insert([
                    'invoice_id' => $invoice->id,
                    'doctor_id' => $invoice->doctor_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        // 3. حذف الأعمدة القديمة التي لم تعد مفيدة لكون الفاتورة متعددة الأطباء
        Schema::table('invoices', function (Blueprint $table) {
             $table->dropForeign(['doctor_id']);
//            $table->dropColumn('doctor_id');
//
//            if (Schema::hasColumn('invoices', 'doctor_commission_percentage')) {
//                $table->dropColumn('doctor_commission_percentage');
//            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_invoice');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->decimal('doctor_commission_percentage', 5, 2)->default(0);
        });
    }
};
