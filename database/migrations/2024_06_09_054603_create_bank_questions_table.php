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
        Schema::create('bank_questions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id');
            $table->string('category_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->foreign('category_id')->references('id')->on('question_categories');
            $table->text('question_text');
            $table->enum('question_type', ['Essay', 'Pilihan Ganda', 'Pilihan Ganda Complex', 'True False']);
            $table->integer('point');
            $table->string('grade_method')->nullable();
            $table->string('course');
            $table->string('class_level');
            $table->boolean('is_required')->default(false);
            $table->string('shared_at');
            $table->integer('shared_count');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_questions');
    }
};
