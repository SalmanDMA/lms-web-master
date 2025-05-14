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
        Schema::create('enrollment_exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_id');
            $table->string('exam_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('school_exams')->onDelete('cascade');
            $table->integer('do_exam')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_exams');
    }
};
