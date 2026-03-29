<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_deals', function (Blueprint $table) {
            $table->dropColumn(['is_prepaid', 'end_date']);
            $table->tinyInteger('status')->default(2)->after('commission_percentage');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_deals', function (Blueprint $table) {
            $table->boolean('is_prepaid')->default(0);
            $table->date('end_date')->nullable();
            $table->dropColumn(['status', 'paid_amount']);
        });
    }
};
