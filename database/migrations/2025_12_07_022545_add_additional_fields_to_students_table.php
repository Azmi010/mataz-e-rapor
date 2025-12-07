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
            $table->string('nisn')->nullable()->after('nis');
            $table->enum('gender', ['L', 'P'])->nullable()->after('nisn');
            $table->string('birth_place')->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('religion')->nullable()->after('birth_date');
            $table->string('previous_school')->nullable()->after('religion');
            $table->date('accepted_date')->nullable()->after('previous_school');
            $table->string('accepted_in_class')->nullable()->after('accepted_date');
            $table->string('father_name')->nullable()->after('accepted_in_class');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('father_occupation')->nullable()->after('mother_name');
            $table->string('mother_occupation')->nullable()->after('father_occupation');
            $table->text('parent_address')->nullable()->after('mother_occupation');
            $table->string('guardian_name')->nullable()->after('parent_address');
            $table->string('guardian_occupation')->nullable()->after('guardian_name');
            $table->text('guardian_address')->nullable()->after('guardian_occupation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nisn',
                'gender',
                'birth_place',
                'birth_date',
                'religion',
                'previous_school',
                'accepted_date',
                'accepted_in_class',
                'father_name',
                'mother_name',
                'father_occupation',
                'mother_occupation',
                'parent_address',
                'guardian_name',
                'guardian_occupation',
                'guardian_address',
            ]);
        });
    }
};
