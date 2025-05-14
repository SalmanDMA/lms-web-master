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
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assignment_id')->nullable();
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->string('assignment_bank_id')->nullable();
            $table->foreign('assignment_bank_id')->references('id')->on('assignment_banks')->onDelete('cascade');
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
        Schema::dropIfExists('assignment_attachments');
    }
};
