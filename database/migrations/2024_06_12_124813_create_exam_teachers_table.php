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
        Schema::create('exam_teachers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id');
            $table->string('exam_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->foreign('exam_id')->references('id')->on('school_exams');
            $table->string('role');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_teachers');
    }
};
