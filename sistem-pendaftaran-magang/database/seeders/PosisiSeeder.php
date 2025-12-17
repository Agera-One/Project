<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosisiSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua jurusan berdasarkan nama_jurusan
        $jurusan = DB::table('jurusan')
            ->pluck('id', 'nama_jurusan')
            ->toArray();

        $data = [
            'Rekayasa Perangkat Lunak' => [
                'Programmer',
                'Web Developer',
                'Mobile Developer',
                'Frontend Developer',
                'Backend Developer',
                'Fullstack Developer',
                'UI/UX Designer',
                'Quality Assurance (QA)',
                'IT Support',
                'Database Administrator',
            ],

            'Akomodasi Perhotelan' => [
                'Front Office',
                'Housekeeping',
                'Room Attendant',
                'Laundry Attendant',
                'Bellboy / Porter',
                'Concierge',
                'Reservation Staff',
                'Guest Service Agent',
                'Public Area',
                'Night Auditor',
            ],

            'Usaha Layanan Wisata' => [
                'Tour Guide',
                'Travel Consultant',
                'Ticketing Staff',
                'Tour Planner',
                'Customer Service Wisata',
                'Marketing Wisata',
                'Event Organizer',
                'Driver Wisata',
                'Admin Tour',
                'Destination Staff',
            ],

            'Tata Boga' => [
                'Commis Chef',
                'Pastry & Bakery',
                'Waiter / Waitress',
                'Barista',
                'Kitchen Steward',
                'Cold Kitchen',
                'Butcher',
                'Food Checker',
                'Banquet Staff',
                'Captain Order',
            ],
        ];

        // Insert ke database
        foreach ($data as $namaJurusan => $daftar) {
            if (! isset($jurusan[$namaJurusan])) {
                $this->command->warn("Jurusan '$namaJurusan' tidak ditemukan di tabel jurusan. Lewati.");
                continue;
            }

            $jurusanId = $jurusan[$namaJurusan];

            foreach ($daftar as $nama) {
                DB::table('posisi')->updateOrInsert(
                    ['jurusan_id' => $jurusanId, 'nama_posisi' => $nama], // cek duplikat
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Berhasil! 10 posisi per jurusan telah di-seed.');
    }
}
