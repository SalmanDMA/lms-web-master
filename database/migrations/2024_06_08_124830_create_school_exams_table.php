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
        Schema::create('school_exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->string('instruction');
            $table->string('course');
            $table->string('status');
            $table->string('publication_status');
            $table->string('class_level');
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
        Schema::dropIfExists('school_exams');
    }
};
