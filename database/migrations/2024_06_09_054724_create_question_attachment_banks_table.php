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
        Schema::create('question_attachment_banks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('question_id');
            $table->foreign('question_id')->references('id')->on('bank_questions')->onDelete('cascade');
            $table->string('file_name');
            $table->text('file_type');
            $table->text('file_url');
            $table->string('file_extension')->nullable();
            $table->string('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_attachment_banks');
    }
};
