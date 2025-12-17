<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class keahlianSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID jurusan dari database
        $jurusan = DB::table('jurusan')->pluck('id', 'nama_jurusan')->toArray();

        $data = [

            'Rekayasa Perangkat Lunak' => [
                'Object-Oriented Programming',
                'Database Querying',
                'API Development',
                'Version Control (Git)',
                'Debugging Techniques',
                'Responsive Web Development',
                'RESTful API Integration',
                'Algorithmic Thinking',
                'Data Structures',
                'Software Testing',
                'Code Optimization',
                'Package Management',
                'Secure Coding Practices',
                'Basic Linux Commands',
                'Deployment Knowledge',
            ],

            'Usaha Layanan Wisata' => [
                'Tour Planning',
                'Customer Service',
                'Communication Skills',
                'Travel Booking Systems',
                'Hospitality Etiquette',
                'Destination Knowledge',
                'Problem Solving',
                'Route Mapping',
                'Itinerary Design',
                'Reservation Handling',
                'Public Speaking',
                'Ticket Coordination',
                'Complaint Handling',
                'Cultural Awareness',
                'Time Management',
            ],

            'Akomodasi Perhotelan' => [
                'Guest Check-In Handling',
                'Reservation Management',
                'Room Preparation',
                'Hospitality Service',
                'Housekeeping Techniques',
                'Customer Interaction',
                'Complaint Resolution',
                'Front Desk Operation',
                'Phone Handling Etiquette',
                'Service Coordination',
                'Food Service Basics',
                'Cleaning Standards',
                'Event Assistance',
                'Cash Handling',
                'Room Inspection',
            ],

            'Tata Boga' => [
                'Knife Skills',
                'Ingredient Preparation',
                'Food Safety & Hygiene',
                'Recipe Measurement',
                'Dough Handling',
                'Baking Techniques',
                'Cooking Methods',
                'Flavor Pairing',
                'Station Organization',
                'Plating Techniques',
                'Kitchen Sanitation',
                'Portioning',
                'Storage Management',
                'Mixing Techniques',
                'Heat Control',
            ],
        ];

        $insert = [];

        foreach ($data as $jurusanName => $skills) {
            $jurusanId = $jurusan[$jurusanName] ?? null;

            if (! $jurusanId) {
                continue;
            }

            foreach ($skills as $skill) {
                $insert[] = [
                    'jurusan_id' => $jurusanId,
                    'nama_keahlian' => $skill,
                ];
            }
        }

        DB::table('keahlian')->insert($insert);
    }
}
