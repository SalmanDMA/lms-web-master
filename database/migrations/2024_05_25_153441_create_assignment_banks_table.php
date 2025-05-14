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
        Schema::create('assignment_banks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('created_by');
            $table->foreign('created_by')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('courses_name');
            $table->foreign('courses_name')->references('id')->on('courses')->onDelete('cascade');
            $table->string('assignment_title');
            $table->text('assignment_description');
            $table->text('instruction');
            $table->string('class_level');
            $table->date('due_date');
            $table->integer('limit_submit');
            $table->boolean('is_visibleGrade')->default(false);
            $table->integer('max_attach');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_banks');
    }
};
