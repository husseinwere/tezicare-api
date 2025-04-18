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
        Schema::create('consultation_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hospital_id');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->decimal('inpatient_nurse_rate', 10, 2)->nullable();
            $table->decimal('inpatient_doctor_rate', 10, 2)->nullable();
            $table->boolean('can_delete')->default(true);
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_types');
    }
};
