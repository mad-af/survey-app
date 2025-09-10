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
            $table->enum('owner_type', ['survey', 'survey_section'])->default('survey')->comment('penentu jenis induk');
            $table->unsignedBigInteger('owner_id')->default(0)->comment('id survey / section');
            $table->string('name');
            $table->timestamps();
            
            $table->index(['owner_type', 'owner_id'], 'idx_result_categories_owner');
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
