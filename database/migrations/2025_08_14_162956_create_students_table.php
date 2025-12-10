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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('class_models')->nullOnDelete();
            $table->string('nis')->unique();
            $table->string('nisn')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->enum('family_status', ['Anak Kandung', 'Anak Tiri', 'Anak Angkat'])->nullable();
            $table->integer('child_order')->nullable();
            $table->string('previous_school')->nullable();
            $table->date('accepted_date')->nullable();
            $table->string('accepted_in_class')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_occupation_other')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_occupation_other')->nullable();
            $table->text('parent_address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_occupation_other')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('wali')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
