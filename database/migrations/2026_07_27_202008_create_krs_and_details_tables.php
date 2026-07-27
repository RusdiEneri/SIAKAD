<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->integer('total_sks')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('krs_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('krs_id')->constrained('krs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->string('status', 20)->default('diambil')->index();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['krs_id', 'kelas_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('krs_details');
        Schema::dropIfExists('krs');
    }
};
