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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            
            // Province data (required)
            $table->string('province_code');
            $table->string('province_name');
            
            // Regency/City data (required)
            $table->string('regency_code');
            $table->string('regency_name');
            
            // District data (nullable)
            $table->string('district_code')->nullable();
            $table->string('district_name')->nullable();
            
            // Village data (nullable)
            $table->string('village_code')->nullable();
            $table->string('village_name')->nullable();
            
            // Detailed address (nullable)
            $table->text('detailed_address')->nullable();
            
            // Coordinates (nullable)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['province_code', 'regency_code']);
            $table->index(['regency_code', 'district_code']);
            $table->index(['district_code', 'village_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
