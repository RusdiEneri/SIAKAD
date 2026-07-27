<?php

namespace App\Models;

use App\Enums\KrsDetailStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KrsDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'krs_details';

    protected $fillable = [
        'krs_id',
        'kelas_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => KrsDetailStatusEnum::class,
        ];
    }

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function nilai(): HasOne
    {
        return $this->hasOne(Nilai::class, 'krs_detail_id');
    }
}
