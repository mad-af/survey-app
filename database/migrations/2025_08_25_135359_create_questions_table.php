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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('survey_sections')->onDelete('cascade');
            $table->text('text');
            $table->enum('type', ['short_text', 'long_text', 'single_choice', 'multiple_choice', 'number', 'date']);
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->decimal('score_weight', 8, 2)->default(0);
            $table->timestamps();
            
            $table->index(['section_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
