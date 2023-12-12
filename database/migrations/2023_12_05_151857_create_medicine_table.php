<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medicine', function (Blueprint $table) {
            $table->id();
            $table->integer('medicalshop_id')->length(11)->default("0");
            $table->string('MedicineName');
            $table->string('product_image', 100)->nullable();
            $table->string('product_description');
            $table->string('product_price', 50)->nullable();
            $table->string('discount')->default('0');
            $table->string('Total_price', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine');
    }
};
