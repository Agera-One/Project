<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Magang SMK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/siswa/lowongan.css') }}">
    <script src="{{ asset('assets/js/siswa/lowongan.js') }}" defer></script>
</head>

<body>
    @include('siswa.layouts.sidebar')

    <div class="container">
        <section class="main-content">
            <div class="department-container">
                <div class="explore-header">
                    <div class="explore-title">
                        <p><img src="{{ asset('assets/images/siswa/dashboard/koper.png') }}" alt=""> Jelajahi
                            Kesempatanmu</p>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search internship positions">
                    </div>
                </div>

                <div class="filter-container">
                    <p>PILIH SESUAI JURUSAN</p>
                    <div class="filter-options"></div>

                    <p class="subfilter-label"></p>
                    <div class="subfilter-container"></div>
                </div>
            </div>

            <div class="main-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <div class="folder-icon"></div>
                    </div>
                    <h2 class="empty-title">
                        Tidak ditemukan magang yang sesuai dengan kriteria Anda
                    </h2>
                    <p class="empty-description">
                        Coba sesuaikan filter atau kueri penelusuran Anda
                    </p>

                    <div class="loading-dots">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>

                    <button class="action-btn" onclick="window.location.href='{{ route('siswa.dashboard') }}'">
                        Kembali ke Semua Magang
                    </button>
                </div>
            </div>

            <div class="internship-container"></div>
        </section>
    </div>
</body>

</html>
