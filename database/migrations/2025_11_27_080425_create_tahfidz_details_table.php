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
        Schema::create('tahfidz_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->onDelete('cascade');
            $table->foreignId('tahfidz_id')->constrained('tahfidz')->onDelete('cascade');
            $table->integer('grade')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfidz_details');
    }
};
