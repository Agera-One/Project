<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lowongan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posisi_id')->nullable()->constrained('posisi')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaan')->cascadeOnDelete();
            $table->foreignId('keahlian_id')->nullable()->constrained('keahlian')->cascadeOnDelete();
            $table->string('deskripsi_lowongan');
            $table->string('durasi_magang');
            $table->integer('jumlah_slot');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
