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
        Schema::create('lab_test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_parameters');
    }
};
