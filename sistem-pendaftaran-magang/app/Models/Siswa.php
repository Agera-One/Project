<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'jurusan_id',
        'nis',
        'kelas',
        'alamat',
        'nomor_telepon',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    // BENAR: satu siswa bisa kirim banyak lamaran
    public function lamaran()
    {
        return $this->hasMany(Lamaran::class, 'siswa_id');
    }

    // BONUS: semua lowongan yang pernah dilamar siswa ini
    public function lowonganDilamar()
    {
        return $this->belongsToMany(Lowongan::class, 'lamaran', 'siswa_id', 'lowongan_id')
            ->withPivot('status', 'tanggal_lamar', 'foto_profil')
            ->withTimestamps();
    }
}
