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
        Schema::create('rpp', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
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
        Schema::dropIfExists('rpp');
    }
};
