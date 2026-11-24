<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingEvaluationAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = ['training_evaluation_id', 'materi', 'nomor', 'nilai'];

    protected function casts(): array
    {
        return [
            'nomor' => 'integer',
            'nilai' => 'integer',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(TrainingEvaluation::class, 'training_evaluation_id');
    }

    /** Teks jawaban: Selalu / Sering / Kadang-Kadang / Tidak Pernah. */
    public function getLabelAttribute(): string
    {
        return TrainingEvaluation::labelNilai($this->nilai);
    }

    /** Teks pertanyaan dari config (kosong bila pertanyaan sudah dihapus dari config). */
    public function getPertanyaanAttribute(): string
    {
        return config("training.materi.{$this->materi}.pertanyaan.{$this->nomor}", '-');
    }
}
