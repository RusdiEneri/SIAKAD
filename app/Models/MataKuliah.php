<?php

namespace App\Models;

use App\Enums\SemesterPenawaranEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'sks',
        'semester_penawaran',
        'prodi_id',
        'is_active',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'semester_penawaran' => SemesterPenawaranEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function prasyarat(): BelongsToMany
    {
        return $this->belongsToMany(
            MataKuliah::class,
            'mata_kuliah_prasyarat',
            'mata_kuliah_id',
            'prasyarat_id'
        );
    }

    public function prasyaratUntuk(): BelongsToMany
    {
        return $this->belongsToMany(
            MataKuliah::class,
            'mata_kuliah_prasyarat',
            'prasyarat_id',
            'mata_kuliah_id'
        );
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
