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
        Schema::create('class_tahfidz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_model_id')->constrained('class_models')->onDelete('cascade');
            $table->foreignId('tahfidz_id')->constrained('tahfidz')->onDelete('cascade');
            $table->timestamps();

            // Tambahkan unique constraint untuk mencegah duplikasi
            $table->unique(['class_model_id', 'tahfidz_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_tahfidz');
    }
};
