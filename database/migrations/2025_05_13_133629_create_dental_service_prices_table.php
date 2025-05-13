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
        Schema::create('dental_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_service_id')->constrained('dental_services')->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained('insurance_covers')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_service_prices');
    }
};
