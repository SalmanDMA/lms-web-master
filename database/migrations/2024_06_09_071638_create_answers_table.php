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
        Schema::create('answers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('response_id');
            $table->string('choice_id')->nullable();
            $table->string('question_id');
            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('response_id')->references('id')->on('responses');
            $table->foreign('choice_id')->references('id')->on('choices');
            $table->text('answer_text');
            $table->boolean('is_graded')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
