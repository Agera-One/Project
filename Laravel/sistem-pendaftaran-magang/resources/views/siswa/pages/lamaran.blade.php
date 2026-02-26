<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Lamaran Magang</title>
    <link rel="stylesheet" href="{{ asset('assets/css/siswa/lamaran.css') }}">
    <script src="{{ asset('assets/js/siswa/lamaran.js') }}" defer></script>
</head>

<body>
    @include('siswa.layouts.sidebar')

    <div class="container">
        <div class="main-content">
            <a href="{{ route('siswa.dashboard') }}" class="back-link">
                <svg viewBox="0 0 24 24">
                    <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" />
                </svg>
                Kembali
            </a>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="form-card">
                <h2 class="form-title">Kirim Lamaran Magang</h2>

                <!-- Info Lowongan -->
                <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:25px;text-align:center;">
                    <h3 style="margin:0 0 8px 0;color:#2c3e50;">
                        {{ $lowongan->posisi?->nama_posisi ?? 'Posisi Magang' }}
                    </h3>
                    <p style="margin:5px 0;font-size:16px;">
                        <strong>{{ $lowongan->perusahaan?->nama_perusahaan ?? 'Perusahaan' }}</strong>
                    </p>
                    <p style="margin:5px 0;color:#666;font-size:14px;">
                        {{ $lowongan->perusahaan?->alamat_perusahaan ?? '-' }}
                        • {{ $lowongan->durasi_magang ?? '-' }}
                    </p>
                </div>

                <form action="{{ route('siswa.lamaran.store', $lowongan->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Foto Formal -->
                    <div class="form-group">
                        <label>Foto Formal (3×4 atau 4×6) <span class="required">*</span></label>
                        <input type="file" name="foto_formal" accept="image/*" required>
                        <small>Maksimal 2MB, format JPG/PNG</small>
                    </div>

                    <!-- CV -->
                    <div class="form-group">
                        <label>CV / Daftar Riwayat Hidup (PDF) <span class="required">*</span></label>
                        <input type="file" name="file_cv" accept=".pdf" required>
                        <small>Maksimal 10MB</small>
                    </div>

                    <!-- Alasan -->
                    <div class="form-group">
                        <label>Alasan Melamar di Perusahaan Ini <span class="required">*</span></label>
                        <textarea name="alasan" rows="5" required placeholder="Tuliskan alasan Anda..."></textarea>
                    </div>

                    <!-- Harapan -->
                    <div class="form-group">
                        <label>Harapan Selama Magang <span class="required">*</span></label>
                        <textarea name="harapan" rows="4" required placeholder="Apa yang ingin Anda capai?"></textarea>
                    </div>

                    <!-- Tombol Kirim & Batal -->
                    <div class="button-group" style="margin-top:35px; text-align:center;">
                        <button type="submit" class="btn btn-primary" style="padding:12px 40px; font-size:16px;">
                            Kirim Lamaran
                        </button>
                        <a href="{{ route('siswa.dashboard') }}" class="btn btn-secondary"
                            style="padding:12px 30px; margin-left:15px;">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
