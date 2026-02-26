<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jurusan;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $JurusanSeeder =
        [
            [
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'nama_jurusan' => 'Akomodasi Perhotelan',
            ],
            [
                'nama_jurusan' => 'Usaha Layanan Wisata',
            ],
            [
                'nama_jurusan' => 'Tata Boga',
            ],
        ];

        foreach ($JurusanSeeder as $data) {
            Jurusan::create($data);
        }
    }
}
