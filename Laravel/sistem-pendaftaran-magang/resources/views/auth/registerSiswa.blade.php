<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth/registerSiswa.css') }}" />
    <script src="{{ asset('assets/js/auth/registerSiswa.js') }}" defer></script>
    <title>Daftar Akun Siswa</title>
</head>

<body>
    <div class="form-container">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
            </svg>
        </div>

        <h1>Daftar Akun Siswa</h1>
        <p class="subtitle">Lengkapi data diri Anda untuk mendaftar</p>

        <form id="studentForm" action="{{ route('register.siswa.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required />
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="text" id="email" name="email" required />
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required />
                </div>

                <div class="form-group">
                    <label for="nis">NIS <span class="required">*</span></label>
                    <input type="text" id="nis" name="nis" required />
                </div>
            </div>


            <div class="form-row">
                <div class="form-group">
                    <label for="kelas">Kelas <span class="required">*</span></label>
                    <input type="text" id="kelas" name="kelas" placeholder="XII RPL 1" required />
                </div>

                <div class="form-group">
                    <label for="jurusan_id">Jurusan <span class="required">*</span></label>
                    <select id="jurusan_id" name="jurusan_id" required>
                        <option value="">Pilih Jurusan</option>
                        @foreach ($jurusan as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir <span class="required">*</span></label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" required />
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir <span class="required">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required />
                </div>
            </div>

            <div class="form-group">
                <label for="nomor_telepon">No. Telepon <span class="required">*</span></label>
                <input type="tel" id="nomor_telepon" name="nomor_telepon" required />
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin <span class="required">*</span></label>
                <input type="text" id="jenis_kelamin" name="jenis_kelamin" required />
            </div>

            <div class="form-group">
                <label for="alamat">Alamat <span class="required">*</span></label>
                <textarea type="text" id="alamat" name="alamat" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required />
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password <span class="required">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required />
                </div>
            </div>

            <div class="info-box">
                Akun siswa akan diverifikasi oleh admin sebelum dapat digunakan.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger"
                    style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 20px;">
                    <strong>Ups! Ada kesalahan:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="btn-primary">Daftar</button>
        </form>

        <div class="footer-link">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
</body>

</html>
