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
        Schema::create('submissions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assignment_id');
            // jangan di on delete cascade untuk assignment id karna ini terhubung juga dengan table grades
            $table->foreign('assignment_id')->references('id')->on('assignments');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('submission_content');
            $table->text('submission_note')->nullable();
            $table->dateTime('submitted_at');
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
