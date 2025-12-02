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
            $table->foreignId('category_id')->nullable()->after('id')->constrained('subject_categories')->nullOnDelete();
            $table->boolean('is_tahfidz')->default(false)->after('type')->comment('Apakah mata pelajaran ini termasuk tahfidz');

            // Hapus kolom yang tidak diperlukan
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'is_tahfidz']);

            // Kembalikan kolom yang dihapus
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('subjects')->nullOnDelete();
            $table->boolean('is_group')->default(false)->after('type');
        });
    }
};
