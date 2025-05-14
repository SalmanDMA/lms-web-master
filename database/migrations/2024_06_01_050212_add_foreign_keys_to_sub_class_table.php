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
        Schema::table('sub_class', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('class_id')->references('id')->on('class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_class', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['class_id']);
        });
    }
};
