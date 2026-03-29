<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('line')->comment('1 for Line 1, 2 for Line 2');
            $table->foreignId('sales_representative_id')->nullable()->constrained('representatives');
            $table->foreignId('medical_representative_id')->nullable()->constrained('representatives');

            $table->timestamps();
        });


    }
};
