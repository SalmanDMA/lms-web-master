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
        Schema::create('grades', function (Blueprint $table) {
            $table->string('id');
            $table->string('submission_id')->nullable();
            $table->string('response_id')->nullable();
            $table->integer('knowledge')->nullable();
            $table->integer('skills')->nullable();
            $table->integer('class_exam')->nullable();
            $table->integer('exam')->nullable();
            $table->string('status')->nullable();
            $table->string('publication_status')->nullable();
            $table->date('graded_at')->nullable();
            $table->boolean('is_main')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
