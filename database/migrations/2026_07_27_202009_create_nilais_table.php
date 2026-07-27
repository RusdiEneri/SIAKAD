<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('krs_detail_id')->unique()->constrained('krs_details')->restrictOnDelete();
            $table->string('nilai_huruf', 5)->index();
            $table->decimal('bobot', 3, 2);
            $table->unsignedTinyInteger('sks');
            $table->boolean('lulus')->index();
            $table->string('tahun_akademik', 20);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
