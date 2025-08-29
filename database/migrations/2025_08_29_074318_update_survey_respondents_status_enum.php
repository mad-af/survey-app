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
        Schema::table('survey_respondents', function (Blueprint $table) {
            // Update status enum to include 'in_progress'
            $table->enum('status', ['invited', 'started', 'in_progress', 'completed', 'expired'])->default('invited')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_respondents', function (Blueprint $table) {
            // Revert status enum to original values
            $table->enum('status', ['invited', 'started', 'completed', 'expired'])->default('invited')->change();
        });
    }
};
