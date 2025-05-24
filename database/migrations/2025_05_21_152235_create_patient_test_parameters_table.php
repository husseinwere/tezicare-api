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
        Schema::create('patient_test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_result_id')->constrained('lab_results')->onDelete('cascade');
            $table->foreignId('lab_test_parameter_id')->constrained('lab_test_parameters')->onDelete('cascade');
            $table->string('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_test_parameters');
    }
};
