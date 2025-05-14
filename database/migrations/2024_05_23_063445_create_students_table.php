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
        Schema::create('students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('sub_class_id');
            $table->foreign('sub_class_id')->references('id')->on('sub_class');
            $table->string('nisn', 50);
            $table->string('major', 50)->nullable();
            $table->enum('type', ['Regular', 'Special'])->default('Regular');
            $table->string('year')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
