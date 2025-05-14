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
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->string('email', 100)->unique();
            $table->string('password', 225)->nullable();
            $table->enum('status', ['Active', 'Non Active', 'Block'])->default('Active');
            $table->text('fullname');
            $table->string('phone', 16)->unique();
            $table->string('gender', 20)->nullable();
            $table->string('religion', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('role', ['STUDENT', 'TEACHER', 'SEKOLAH', 'STAFF']);
            $table->text('image_path')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->text('fcm_token')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
