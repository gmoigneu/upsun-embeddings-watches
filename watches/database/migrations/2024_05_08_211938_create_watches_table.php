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
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->string('case_material')->nullable();
            $table->string('strap_material')->nullable();
            $table->string('movement_type')->nullable();
            $table->string('water_resistance')->nullable();
            $table->decimal('case_diameter_mm', 5, 2)->nullable();
            $table->decimal('case_thickness_mm', 5, 2)->nullable();
            $table->decimal('band_width_mm', 5, 2)->nullable();
            $table->string('dial_color')->nullable();
            $table->string('crystal_material')->nullable();
            $table->string('complications')->nullable();
            $table->string('power_reserve')->nullable();
            $table->integer('price_usd')->nullable();
            $table->vector('embedding', 1536)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watches');
    }
};
