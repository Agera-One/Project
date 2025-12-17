<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SiswaModel;
use App\Models\PosisiModel;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';

    public $timestamps = true;

    protected $fillable = ['nama_jurusan'];

    public function posisi()
    {
        return $this->hasMany(Posisi::class, 'posisi_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'siswa_id');
    }
}
