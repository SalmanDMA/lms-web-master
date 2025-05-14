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
        Schema::create('questions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('class_exams');
            $table->string('school_exam_id')->nullable();
            $table->foreign('school_exam_id')->references('id')->on('school_exams');
            $table->string('category_id');
            $table->foreign('category_id')->references('id')->on('question_categories');
            $table->string('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('exam_sections');
            $table->text('question_text');
            $table->enum('question_type', ['Essay', 'Pilihan Ganda', 'Pilihan Ganda Complex', 'True False']);
            $table->integer('point');
            $table->string('grade_method')->nullable();
            $table->string('difficult');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
