<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('documents')->insert([
            [
                'name' => 'Laporan Keuangan Q1 2025.xlsx',
                'extension' => 'xlsx',
                'status' => 'active',
                'size' => 2516582,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Proposal Pengembangan Sistem Arsip.docx',
                'extension' => 'docx',
                'status' => 'active',
                'size' => 1887437,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Notulen Rapat Evaluasi Proyek.docx',
                'extension' => 'docx',
                'status' => 'archived',
                'size' => 634880,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Surat Keputusan Direktur Operasional.pdf',
                'extension' => 'pdf',
                'status' => 'archived',
                'size' => 460800,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dokumentasi API Internal v2.pdf',
                'extension' => 'pdf',
                'status' => 'active',
                'size' => 3355443,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Panduan Penggunaan Aplikasi Inventaris.pdf',
                'extension' => 'pdf',
                'status' => 'active',
                'size' => 2202009,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kontrak Kerja Sama Vendor IT.pdf',
                'extension' => 'pdf',
                'status' => 'deleted',
                'size' => 1572864,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Laporan Audit Internal Tahunan.xlsx',
                'extension' => 'xlsx',
                'status' => 'active',
                'size' => 3040870,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Rencana Anggaran Biaya Proyek.xlsx',
                'extension' => 'xlsx',
                'status' => 'active',
                'size' => 1258291,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Berita Acara Serah Terima Dokumen.docx',
                'extension' => 'docx',
                'status' => 'archived',
                'size' => 716800,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
