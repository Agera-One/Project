<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongan';

    public $timestamps = true;

    protected $fillable = [
        'perusahaan_id',
        'posisi_id',
        'jurusan_id',
        'keahlian_id',
        'deskripsi_lowongan',
        'durasi_magang',
        'jumlah_slot',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'posisi_id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function keahlian()
    {
        return $this->belongsTo(Keahlian::class, 'keahlian_id');
    }

    // BENAR: satu lowongan bisa punya banyak lamaran
    public function lamaran()
    {
        return $this->hasMany(Lamaran::class, 'lowongan_id');
    }

    // BONUS: semua siswa yang melamar lowongan ini
    public function pelamar()
    {
        return $this->belongsToMany(Siswa::class, 'lamaran', 'lowongan_id', 'siswa_id')
            ->withPivot('status', 'tanggal_lamar', 'foto_profil')
            ->withTimestamps();
    }
}
