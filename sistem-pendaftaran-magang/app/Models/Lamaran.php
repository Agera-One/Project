<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamaran extends Model
{
    use HasFactory;

    protected $table = 'lamaran';
    
    public $timestamps = true;

    protected $fillable = [
        'siswa_id',
        'lowongan_id',
        'status',
        'tanggal_lamar',
        'foto_formal',
        'file_cv',
        'alasan',
        'harapan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function lowongan()
    {
        return $this->belongsTo(Lowongan::class, 'lowongan_id');
    }
}
