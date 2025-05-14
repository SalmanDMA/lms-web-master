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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('created_by');
            $table->foreign('created_by')->references('id')->on('schools');
            $table->string('courses_title');
            $table->text('courses_description')->nullable();
            $table->string('type')->nullable();
            $table->string('curriculum')->nullable();
            $table->string('course_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
