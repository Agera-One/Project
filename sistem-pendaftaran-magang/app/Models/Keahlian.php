<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JurusanModel;

class Keahlian extends Model
{
    use HasFactory;

    protected $table = 'keahlian';

    public $timestamps = true;

    protected $fillable = [
        'jurusan_id',
        'nama_keahlian',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
}
