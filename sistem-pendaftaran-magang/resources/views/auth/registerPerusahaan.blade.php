<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth/registerPerusahaan.css') }}" />
    <script src="{{ asset('assets/js/auth/registerPerusahaan.js') }}" defer></script>
    <title>Daftar Akun Perusahaan</title>
</head>

<body>
    <div class="form-container">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M5,3V21H11V17.5H13V21H19V3H5M7,5H9V7H7V5M11,5H13V7H11V5M15,5H17V7H15V5M7,9H9V11H7V9M11,9H13V11H11V9M15,9H17V11H15V9M7,13H9V15H7V13M11,13H13V15H11V13M15,13H17V15H15V13M7,17H9V19H7V17M15,17H17V19H15V17Z" />
            </svg>
        </div>

        <h1>Daftar Akun Perusahaan</h1>
        <p class="subtitle">Lengkapi data perusahaan Anda untuk mendaftar</p>

        <form id="companyForm" action="{{ route('register.perusahaan.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="username">
                        Username Perusahaan <span class="required">*</span>
                    </label>
                    <input type="text" id="username" name="username" required />
                </div>

                <div class="form-group">
                    <label for="email">
                        Email Perusahaan <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" required />
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

                <div class="form-group">
                    <label for="nomor_telepon">No. Telepon <span class="required">*</span></label>
                    <input type="tel" id="nomor_telepon" name="nomor_telepon" required />
                </div>
            </div>

            <div class="form-group">
                <label for="nama_perusahaan">Nama Perusahaan <span class="required">*</span></label>
                <input type="text" id="nama_perusahaan" name="nama_perusahaan"
                    placeholder="Google, Meta, Amazon, dll." required />
            </div>

            <div class="form-group">
                <label for="alamat_perusahaan">Alamat Perusahaan<span class="required">*</span></label>
                <textarea type="text" id="alamat_perusahaan" name="alamat_perusahaan" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        Password <span class="required">*</span>
                    </label>
                    <input type="password" id="password" name="password" required />
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        Konfirmasi Password <span class="required">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required />
                </div>
            </div>

            <div class="info-box">
                Akun perusahaan akan diverifikasi oleh admin sebelum dapat digunakan.
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
    </div>
</body>

</html>
