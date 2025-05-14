<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('learning_id');
            $table->foreign('learning_id')->references('id')->on('learnings')->onDelete('cascade');
            $table->string('material_title');
            $table->text('material_description');
            $table->string('class_level');
            $table->dateTime('shared_at');
            $table->string('publication_status');
            $table->string('status');
            $table->integer('max_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
