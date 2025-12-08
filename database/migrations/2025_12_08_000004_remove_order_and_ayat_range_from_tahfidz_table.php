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
        Schema::table('tahfidz', function (Blueprint $table) {
            $table->dropColumn(['order', 'ayat_range']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahfidz', function (Blueprint $table) {
            $table->string('ayat_range')->nullable()->comment('Range ayat, contoh: 1-7');
            $table->integer('order')->default(1)->comment('Urutan');
        });
    }
};
