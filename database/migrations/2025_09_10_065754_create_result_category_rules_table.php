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
        Schema::create('result_category_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_category_id')->constrained('result_categories')->onDelete('cascade');
            $table->enum('operation', ['lt', 'gt', 'else']);
            $table->decimal('score', 10, 2)->comment('untuk single-bound');
            $table->string('color')->comment('kelas DaisyUI, contoh: primary, success');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_category_rules');
    }
};
