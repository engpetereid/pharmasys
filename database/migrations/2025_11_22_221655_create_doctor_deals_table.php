<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('pharmacist_id')->constrained()->onDelete('cascade');
            $table->decimal('target_amount', 12, 2)->nullable();
            $table->decimal('achieved_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->boolean('is_prepaid')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_deals');
    }
};
