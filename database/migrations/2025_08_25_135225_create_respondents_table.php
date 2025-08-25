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
        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique()->comment('ID dari SSO/HRIS/StudentID');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say']);
            $table->integer('birth_year')->nullable();
            $table->string('organization')->nullable();
            $table->string('department')->nullable();
            $table->string('role_title')->nullable();
            $table->string('location')->nullable();
            $table->json('demographics')->nullable()->comment('Field bebas untuk education, income, dsb');
            $table->timestamp('consent_at')->nullable()->comment('Timestamp persetujuan');
            $table->timestamps();
            
            $table->index('email');
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respondents');
    }
};
