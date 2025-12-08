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
        Schema::table('subjects', function (Blueprint $table) {
            // Hapus kolom yang tidak diperlukan karena tahfidz sudah terpisah
            $table->dropColumn(['is_tahfidz', 'has_details']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_tahfidz')->default(false);
            $table->boolean('has_details')->default(false);
        });
    }
};
