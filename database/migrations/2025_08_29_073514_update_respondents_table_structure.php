<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            // Make email nullable
            $table->string('email')->nullable()->change();
            
            // Add gender enum column as nullable first
            $table->enum('gender', ['male', 'female'])->nullable()->after('phone');
        });
        
        // Set default gender for existing records
        DB::statement("UPDATE respondents SET gender = 'male' WHERE gender IS NULL");
        
        // Make gender not nullable after setting default values
        Schema::table('respondents', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respondents', function (Blueprint $table) {
            // Revert email to not nullable
            $table->string('email')->nullable(false)->change();
            
            // Revert gender enum to original values
            $table->dropColumn('gender');
        });
        
        Schema::table('respondents', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->after('phone');
        });
    }
};
