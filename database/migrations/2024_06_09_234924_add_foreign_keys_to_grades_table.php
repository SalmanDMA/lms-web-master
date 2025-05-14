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
        Schema::table('grades', function (Blueprint $table) {
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
            $table->foreign('response_id')->references('id')->on('responses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropForeign(['response_id']);
        });
    }
};
