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
        Schema::create('rpp_draft', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->string('rpp_id')->nullable();
            $table->foreign('rpp_id')->references('id')->on('rpp')->onDelete('cascade');
            $table->string('rpp_bank_id')->nullable();
            $table->foreign('rpp_bank_id')->references('id')->on('rpp_bank')->onDelete('cascade');
            $table->string('courses');
            $table->string('class_level');
            $table->string('draft_name');
            $table->string('status');
            $table->string('academic_year');
            $table->string('semester');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('rpp_draft', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['rpp_id']);
            $table->dropForeign(['rpp_bank_id']);
        });

        // Drop the tables
        Schema::dropIfExists('rpp_draft');
        Schema::dropIfExists('rpp');
    }
};
