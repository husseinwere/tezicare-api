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
        Schema::create('patient_non_pharmaceuticals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('patient_sessions');
            $table->unsignedBigInteger('non_pharmaceutical_id');
            $table->foreign('non_pharmaceutical_id')->references('id')->on('non_pharmaceuticals');
            $table->integer('quantity')->unsigned();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('payment_status')->default('NOT_PAID');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_non_pharmaceuticals');
    }
};
