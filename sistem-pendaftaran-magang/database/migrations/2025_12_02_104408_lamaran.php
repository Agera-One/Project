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
        Schema::create('lamaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('lowongan_id')->nullable()->constrained('lowongan')->cascadeOnDelete();
            $table->enum('status', ['tertunda', 'diterima', 'ditolak'])->default('tertunda');
            $table->timestamp('tanggal_lamar')->useCurrent();
            $table->string('foto_formal');
            $table->string('file_cv');
            $table->string('alasan');
            $table->string('harapan');
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
