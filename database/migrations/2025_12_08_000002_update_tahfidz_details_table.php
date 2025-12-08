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
        Schema::table('tahfidz_details', function (Blueprint $table) {
            // Drop foreign key lama ke subjects
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
            
            // Hapus kolom surah dan ayat karena sudah ada di tabel tahfidz
            $table->dropColumn(['surah', 'ayat']);
            
            // Tambah foreign key ke tahfidz
            $table->foreignId('tahfidz_id')->after('report_card_id')->constrained('tahfidz')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahfidz_details', function (Blueprint $table) {
            $table->dropForeign(['tahfidz_id']);
            $table->dropColumn('tahfidz_id');
            
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('surah')->nullable();
            $table->string('ayat')->nullable();
        });
    }
};
