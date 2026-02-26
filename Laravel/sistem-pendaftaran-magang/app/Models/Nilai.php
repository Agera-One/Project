<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai'; // eksplisit karena nama tabel tidak plural

    protected $fillable = [
        'siswa_id',
        'nilai_disiplin',
        'nilai_kerajinan',
        'nilai_total',
    ];

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Optional: auto-calculate nilai_total saat save
    protected static function booted()
    {
        static::saving(function ($nilai) {
            if ($nilai->nilai_disiplin !== null && $nilai->nilai_kerajinan !== null) {
                $nilai->nilai_total = (int) (($nilai->nilai_disiplin + $nilai->nilai_kerajinan) / 2);
            }
        });
    }
}
