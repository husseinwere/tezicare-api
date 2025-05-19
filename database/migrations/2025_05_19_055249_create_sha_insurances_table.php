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
        Schema::create('sha_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_id')->constrained('insurance_covers')->onDelete('cascade');
            $table->string('rebate_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sha_insurances');
    }
};
