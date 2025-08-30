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
        Schema::table('responses', function (Blueprint $table) {
            $table->enum('status', ['started', 'in_progress', 'completed', 'abandoned'])
                  ->default('started')
                  ->after('current_step')
                  ->comment('Response status: started, in_progress, completed, abandoned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
