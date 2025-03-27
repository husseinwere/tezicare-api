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
        Schema::create('ward_round_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ward_round_id');
            $table->foreign('ward_round_id')->references('id')->on('ward_rounds');
            $table->string('record_type');
            $table->string('description');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
            $table->string('status')->default('ACTIVE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_round_records');
    }
};
