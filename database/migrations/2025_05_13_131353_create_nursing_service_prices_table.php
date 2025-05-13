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
        Schema::create('nursing_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nursing_service_id')->constrained('nursing_services')->onDelete('cascade');
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
        Schema::dropIfExists('nursing_service_prices');
    }
};
