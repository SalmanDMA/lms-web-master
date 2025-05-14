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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->string('splash_logo');
            $table->string('splash_title');
            $table->string('login_image_student');
            $table->string('login_image_teacher');
            $table->string('title');
            $table->string('logo');
            $table->string('logo_thumbnail');
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->string('accent_color');
            $table->string('white_color');
            $table->string('black_color');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
