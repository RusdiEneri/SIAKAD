<?php

namespace App\Models;

use App\Enums\KrsStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Krs extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'krs';

    protected $fillable = [
        'mahasiswa_id',
        'semester_id',
        'total_sks',
        'status',
        'disetujui_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'total_sks' => 'integer',
            'status' => KrsStatusEnum::class,
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'krs_id');
    }
}
