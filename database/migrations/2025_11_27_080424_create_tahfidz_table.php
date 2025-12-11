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
        Schema::create('tahfidz', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama Surat/Juz');
            $table->string('arabic_name')->nullable()->comment('Nama Arab');
            $table->text('description')->nullable();
            $table->integer('juz')->nullable()->comment('Nomor Juz');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfidz');
    }
};
