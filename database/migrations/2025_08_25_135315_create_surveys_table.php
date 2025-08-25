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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('code')->unique()->comment('slug/link');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->boolean('is_anonymous')->default(false);
            $table->enum('visibility', ['private', 'link', 'public'])->default('private');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('status');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
