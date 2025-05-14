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
        Schema::create('schools', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('admin_email', 100);
            $table->string('admin_password', 225)->nullable();
            $table->string('admin_name');
            $table->string('admin_phone', 16)->unique();
            $table->text('admin_address')->nullable();
            $table->string('logo')->nullable();
            $table->string('school_image')->nullable();
            $table->string('structure')->nullable();
            $table->string('phone_number', 16)->unique();
            $table->string('email', 100);
            $table->string('website')->nullable();
            $table->string('name');
            $table->string('another_name')->nullable();
            $table->string('type');
            $table->string('status');
            $table->char('acreditation', 1)->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('description')->nullable();
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('neighborhood');
            $table->string('rw');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('address');
            $table->integer('pos')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->dateTime('premium_expired_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
