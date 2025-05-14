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
        Schema::create('material_resources', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('material_bank_id')->nullable();
            $table->foreign('material_bank_id')->references('id')->on('material_banks')->onDelete('cascade');
            $table->string('material_id')->nullable();
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->string('resource_name');
            $table->string('resource_type');
            $table->string('resource_url');
            $table->string('resource_extension')->nullable();
            $table->string('resource_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_resources');
    }
};
