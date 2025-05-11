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
        Schema::create('ward_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hospital_id');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('patient_sessions');
            $table->unsignedBigInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds');
            $table->decimal('bed_price', 10, 2);
            $table->decimal('doctor_price', 10, 2);
            $table->decimal('nurse_price', 10, 2);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_rounds');
    }
};
