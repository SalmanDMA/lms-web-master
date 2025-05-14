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
        Schema::create('course_teacher', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_teacher');
    }
};
