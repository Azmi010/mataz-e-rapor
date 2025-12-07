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
        Schema::table('students', function (Blueprint $table) {
            $table->enum('family_status', ['Anak Kandung', 'Anak Tiri', 'Anak Angkat'])->nullable()->after('religion');
            $table->integer('child_order')->nullable()->after('family_status');
            $table->string('father_occupation_other')->nullable()->after('father_occupation');
            $table->string('mother_occupation_other')->nullable()->after('mother_occupation');
            $table->string('guardian_occupation_other')->nullable()->after('guardian_occupation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['family_status', 'child_order', 'father_occupation_other', 'mother_occupation_other', 'guardian_occupation_other']);
        });
    }
};
