<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'jurusan_id',
        'nama_perusahaan',
        'alamat_perusahaan',
        'nomor_telepon',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    // BENAR: satu perusahaan bisa buat banyak lowongan
    public function lowongan()
    {
        return $this->hasMany(Lowongan::class, 'perusahaan_id');
    }
}
