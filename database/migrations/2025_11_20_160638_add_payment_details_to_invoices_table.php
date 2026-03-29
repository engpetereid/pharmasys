<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('final_total');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('paid_amount');
            $table->decimal('doctor_commission_percentage', 5, 2)->default(0)->after('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'remaining_amount', 'doctor_commission_percentage']);
        });
    }
};
