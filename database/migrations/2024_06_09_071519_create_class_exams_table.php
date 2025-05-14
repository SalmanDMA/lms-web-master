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
        Schema::create('class_exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('learning_id');
            $table->foreign('learning_id')->references('id')->on('learnings')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('type');
            $table->text('instruction');
            $table->boolean('is_active')->default(false);
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_exams');
    }
};
