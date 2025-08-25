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
        Schema::create('result_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_score', 8, 2);
            $table->decimal('max_score', 8, 2);
            $table->string('color', 7)->default('#6B7280')->comment('hex color');
            $table->timestamps();
            
            $table->index(['survey_id', 'min_score', 'max_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_categories');
    }
};
