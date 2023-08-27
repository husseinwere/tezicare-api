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
        Schema::create('patient_drugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('patient_sessions');
            $table->unsignedBigInteger('drug_id');
            $table->foreign('drug_id')->references('id')->on('pharmaceuticals');
            $table->string('dosage');
            $table->quantity('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->string('treatment')->default('DISCHARGE');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('payment_status')->default('NOT_PAID');
            $table->timestamps();
            $table->string('status')->default('ACTIVE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_drugs');
    }
};
