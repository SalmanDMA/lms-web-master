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
        Schema::create('choices_question_banks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('question_id');
            $table->foreign('question_id')->references('id')->on('bank_questions')->onDelete('cascade');
            $table->string('choice_text');
            $table->boolean('is_true')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices_question_banks');
    }
};
