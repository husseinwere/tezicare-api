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
        Schema::create('consultation_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultation_types')->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained('insurance_covers')->onDelete('cascade');
            $table->decimal('consultation_price', 10, 2)->nullable();
            $table->decimal('inpatient_doctor_price', 10, 2)->nullable();
            $table->decimal('inpatient_nurse_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_prices');
    }
};
