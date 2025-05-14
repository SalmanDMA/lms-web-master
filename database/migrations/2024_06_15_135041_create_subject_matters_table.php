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
        Schema::create('subject_matters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('rpp_id')->nullable();
            $table->foreign('rpp_id')->references('id')->on('rpp')->onDelete('cascade');
            $table->string('rpp_bank_id')->nullable();
            $table->foreign('rpp_bank_id')->references('id')->on('rpp_bank')->onDelete('cascade');
            $table->string('rpp_draft_id')->nullable();
            $table->foreign('rpp_draft_id')->references('id')->on('rpp_draft')->onDelete('cascade');
            $table->string('title');
            $table->time('time_allocation');
            $table->text('learning_goals');
            $table->text('learning_activity');
            $table->text('grading');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_matters');
    }
};
