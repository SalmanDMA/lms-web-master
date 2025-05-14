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
        Schema::create('material_banks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('created_by');
            $table->foreign('created_by')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('course_title');
            $table->foreign('course_title')->references('id')->on('courses')->onDelete('cascade');
            $table->string('material_title');
            $table->text('material_description');
            $table->string('class_level');
            $table->dateTime('shared_at');
            $table->integer('max_attach');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_banks');
    }
};
