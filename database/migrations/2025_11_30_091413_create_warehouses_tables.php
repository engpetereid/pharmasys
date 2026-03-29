<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['main', 'sub'])->default('sub');
            $table->foreignId('parent_id')->nullable()->constrained('warehouses')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('drug_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('drug_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->unique(['warehouse_id', 'drug_id']);
            $table->timestamps();
        });


        Schema::table('zones', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('province_id')->constrained('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::dropIfExists('drug_warehouse');
        Schema::dropIfExists('warehouses');
    }
};
