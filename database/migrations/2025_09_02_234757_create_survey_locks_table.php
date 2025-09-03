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
        Schema::create('survey_locks', function (Blueprint $table) {
            $table->id();
            $table->string('lock_key')->unique()->index();
            $table->string('process_id')->nullable();
            $table->unsignedBigInteger('response_id')->nullable();
            $table->string('operation_type')->default('submit'); // submit, partial_submit
            $table->timestamp('acquired_at');
            $table->timestamp('expires_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('response_id')->references('id')->on('responses')->onDelete('cascade');
            
            // Index for performance
            $table->index(['expires_at']);
            $table->index(['response_id', 'operation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_locks');
    }
};
