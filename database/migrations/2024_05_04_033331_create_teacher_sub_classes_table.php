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
        Schema::create('teacher_sub_class', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id')->nullable();
            $table->string('sub_class_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->foreign('sub_class_id')->references('id')->on('sub_class');
            $table->string('course')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_sub_class');
    }
};
