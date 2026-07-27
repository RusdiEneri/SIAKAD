<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->unsignedTinyInteger('sks');
            $table->string('semester_penawaran', 20)->default('semua')->index();
            $table->foreignId('prodi_id')->constrained('prodis')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('mata_kuliah_prasyarat', function (Blueprint $table) {
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->foreignId('prasyarat_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->primary(['mata_kuliah_id', 'prasyarat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah_prasyarat');
        Schema::dropIfExists('mata_kuliahs');
    }
};
