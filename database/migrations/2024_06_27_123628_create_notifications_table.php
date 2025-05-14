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
        Schema::create('notifications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_id')->nullable();
            $table->string('teacher_id')->nullable();
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->string('type');
            $table->string('title');
            $table->string('message');
            $table->string('is_read')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
