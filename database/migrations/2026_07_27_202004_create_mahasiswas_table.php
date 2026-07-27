<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nim', 30)->unique();
            $table->foreignId('prodi_id')->constrained('prodis')->restrictOnDelete();
            $table->integer('angkatan')->index();
            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->string('status', 20)->default('aktif')->index();
            $table->date('tanggal_masuk');
            $table->date('tanggal_lulus')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
