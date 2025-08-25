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
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('respondent_id')->nullable()->constrained('respondents')->onDelete('set null')->comment('nullable untuk anonim');
            $table->uuid('respondent_token')->nullable()->unique()->comment('untuk anonim unik');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->json('meta')->nullable()->comment('ip, ua, device');
            $table->timestamps();
            
            $table->index(['survey_id', 'submitted_at']);
            $table->index('respondent_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
