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
        Schema::create('learnings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->enum('status', ['Active', 'Non Active'])->default('Active');
            $table->string('course');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learnings');
    }
};
