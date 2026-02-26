<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JurusanModel;

class Posisi extends Model
{
    use HasFactory;

    protected $table = 'posisi';

    public $timestamps = true;

    protected $fillable = [
        'jurusan_id',
        'nama_posisi',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
}
