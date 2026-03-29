<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('doctor_deals', function (Blueprint $table) {
            $table->dropForeign(['pharmacist_id']);
            $table->dropColumn('pharmacist_id');
            $table->decimal('commission_percentage', 5, 2)->default(0)->after('commission_amount');
        });

        Schema::create('deal_pharmacist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_deal_id')->constrained('doctor_deals')->onDelete('cascade');
            $table->foreignId('pharmacist_id')->constrained('pharmacists')->onDelete('cascade');
        });

        Schema::create('deal_drug', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_deal_id')->constrained('doctor_deals')->onDelete('cascade');
            $table->foreignId('drug_id')->constrained('drugs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_drug');
        Schema::dropIfExists('deal_pharmacist');

        Schema::table('doctor_deals', function (Blueprint $table) {
            $table->foreignId('pharmacist_id')->nullable()->constrained('pharmacists')->onDelete('cascade');
            $table->dropColumn('commission_percentage');
        });
    }
};
