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
        Schema::table('respondents', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['location', 'demographics']);
            
            // Add location_id foreign key
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            // Drop foreign key and location_id column
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
            
            // Add back the original columns
            $table->text('location')->nullable();
            $table->json('demographics')->nullable();
        });
    }
};
