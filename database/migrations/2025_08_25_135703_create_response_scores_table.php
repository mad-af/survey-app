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
        Schema::create('response_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->onDelete('cascade');
            $table->foreignId('result_category_id')->nullable()->constrained('result_categories')->onDelete('set null');
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('max_possible_score', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0)->comment('0-100');
            $table->json('section_scores')->nullable()->comment('breakdown per section');
            $table->timestamps();
            
            $table->unique('response_id');
            $table->index('total_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_scores');
    }
};
