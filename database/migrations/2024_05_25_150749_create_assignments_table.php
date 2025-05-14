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
        Schema::create('assignments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('learning_id');
            $table->foreign('learning_id')->references('id')->on('learnings')->onDelete('cascade');
            $table->string('assignment_title');
            $table->text('assignment_description');
            $table->text('instruction');
            $table->date('due_date');
            $table->time('end_time');
            $table->enum('collection_type', ['Catatan', 'Lampiran', 'All']);
            $table->integer('limit_submit');
            $table->string('class_level');
            $table->boolean('is_visibleGrade')->default(false);
            $table->string('publication_status');
            $table->integer('max_attach');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
