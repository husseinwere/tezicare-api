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
        Schema::table('ward_rounds', function (Blueprint $table) {
            // $table->unsignedBigInteger('hospital_id')->after('id');
            $table->foreign('hospital_id')->references('id')->on('hospitals');
            // $table->unsignedBigInteger('created_by')->after('nurse_price');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ward_rounds', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            // $table->dropColumn('hospital_id');
            $table->dropForeign(['created_by']);
            // $table->dropColumn('created_by');
        });
    }
};
