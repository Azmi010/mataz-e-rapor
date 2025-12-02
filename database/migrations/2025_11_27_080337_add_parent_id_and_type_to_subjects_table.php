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
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('subjects')->nullOnDelete();
            $table->enum('type', ['regular', 'tahfidz'])->default('regular')->after('name');
            $table->boolean('is_group')->default(false)->after('type')->comment('Apakah mata pelajaran ini grup/induk yang memiliki sub-mapel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'type', 'is_group']);
        });
    }
};
