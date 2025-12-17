<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth/registerChoose.css') }}">
    <title>Daftar Akun Baru</title>
</head>

<body>
    <div class="container">
        <h1>Daftar Akun Baru</h1>
        <p class="subtitle">Pilih jenis akun yang ingin Anda buat</p>

        <div class="cards-container">
            <div class="card student">
                <div class="icon-circle">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
                    </svg>
                </div>
                <h2>Akun Siswa</h2>
                <p>Daftar sebagai siswa untuk mencari dan melamar posisi magang</p>
                <a href="{{ route('register.siswa.form') }}" class="btn btn-student">
                    Daftar Sebagai Siswa
                </a>
            </div>

            <div class="card company">
                <div class="icon-circle">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M5,3V21H11V17.5H13V21H19V3H5M7,5H9V7H7V5M11,5H13V7H11V5M15,5H17V7H15V5M7,9H9V11H7V9M11,9H13V11H11V9M15,9H17V11H15V9M7,13H9V15H7V13M11,13H13V15H11V13M15,13H17V15H15V13M7,17H9V19H7V17M15,17H17V19H15V17Z" />
                    </svg>
                </div>
                <h2>Akun Perusahaan</h2>
                <p>Daftar sebagai perusahaan untuk memposting lowongan magang</p>
                <a href="{{ route('register.perusahaan.form') }}" class="btn btn-company">
                    Daftar Sebagai Perusahaan
                </a>
            </div>
        </div>

        <div class="footer-link">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>
</body>

</html>
