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
        Schema::create('exam_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_id')->nullable();
            $table->string('school_exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('class_exams')->onDelete('cascade');
            $table->foreign('school_exam_id')->references('id')->on('school_exams')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('token')->nullable();
            $table->string('token_expiration')->nullable(); // untuk kalau curang akan di buat token baru selama 5 menit
            $table->time('duration');
            $table->integer('repeat_chance');
            $table->enum('device', ['Web', 'Mobile', 'All']);
            $table->integer('maximum_user');
            $table->boolean('is_random_question')->default(false);
            $table->boolean('is_random_answer')->default(false);
            $table->boolean('is_show_score')->default(false);
            $table->boolean('is_show_result')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_settings');
    }
};
