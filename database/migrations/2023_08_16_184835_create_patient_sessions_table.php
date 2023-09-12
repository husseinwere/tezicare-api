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
        Schema::create('patient_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->string('patient_type')->default('OUTPATIENT');
            $table->string('consultation_type')->default('GENERAL');
            $table->integer('registration_fee')->nullable();
            $table->integer('consultation_fee')->nullable();
            $table->integer('admission_fee')->nullable();
            $table->integer('bed_fee')->nullable();
            $table->integer('doctor_fee')->nullable();
            $table->integer('nurse_fee')->nullable();
            $table->dateTime('discharged')->nullable();
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
        Schema::dropIfExists('patient_sessions');
    }
};
