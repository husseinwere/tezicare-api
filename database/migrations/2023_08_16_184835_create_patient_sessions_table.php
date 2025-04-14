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
            $table->unsignedBigInteger('hospital_id');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
            $table->unsignedBigInteger('invoice_number');
            $table->unique(['hospital_id', 'invoice_number']);
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->string('patient_type')->default('OUTPATIENT');
            $table->unsignedBigInteger('consultation_type');
            $table->foreign('consultation_type')->references('id')->on('consultation_types');
            $table->decimal('registration_fee', 10, 2)->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('users');
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
