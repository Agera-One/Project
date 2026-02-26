<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('assets/css/auth/login.css') }}" />
    <title>Portal Magang - Login</title>
</head>

<body>
    <div class="login-container">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
            </svg>
        </div>

        <h1>Portal Magang</h1>
        <p class="subtitle">Masuk ke akun Anda</p>

        <form id="loginForm" action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="login">Username atau Email</label>
                <input type="text" id="login" name="login" value="{{ old('login') }}"
                    placeholder="Masukkan username atau email" autofocus required />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required />
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

            <button type="submit" class="btn-primary">Masuk</button>
        </form>

        <div class="footer-link">
            Belum punya akun? <a href="{{ route('register.choose') }}">Daftar sekarang</a>
        </div>
    </div>
</body>

</html>
