<?php

namespace App\Models;

use App\Enums\NilaiHurufEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nilai extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'krs_detail_id',
        'nilai_huruf',
        'bobot',
        'sks',
        'lulus',
        'tahun_akademik',
    ];

    protected function casts(): array
    {
        return [
            'nilai_huruf' => NilaiHurufEnum::class,
            'bobot' => 'decimal:2',
            'sks' => 'integer',
            'lulus' => 'boolean',
        ];
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}
