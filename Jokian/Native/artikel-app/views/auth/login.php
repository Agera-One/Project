<?php
// views/auth/login.php
$pageTitle = 'Masuk — ' . APP_NAME;
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
</head>
<body>

<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>" id="flashMsg">
  <span><?= $flash['message'] ?></span>
  <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
</div>
<?php endif; ?>

<div class="auth-wrapper">
  <!-- Visual Side -->
  <div class="auth-visual">
    <div class="auth-visual-brand">
      <span class="brand-icon">◈</span>
      <span class="brand-text"><?= APP_NAME ?></span>
    </div>
    <div class="auth-visual-quote">
      <blockquote>"Membaca adalah jendela dunia yang tak pernah tertutup."</blockquote>
      <cite>— Peribahasa Indonesia</cite>
    </div>
    <p class="auth-tagline"><?= APP_NAME ?> · Platform Artikel Terpercaya</p>
  </div>

  <!-- Form Side -->
  <div class="auth-form-side">
    <div class="auth-form-inner fade-up">
      <div class="auth-form-header">
        <h1 class="auth-form-title">Selamat Datang</h1>
        <p class="auth-form-subtitle">Masuk untuk melanjutkan membaca artikel pilihan.</p>
      </div>

      <form method="POST" action="<?= APP_URL ?>/auth/login">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" name="username"
                 class="form-control"
                 placeholder="Masukkan username kamu"
                 value="<?= e(post('username')) ?>"
                 autocomplete="username" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password"
                 class="form-control"
                 placeholder="Masukkan password kamu"
                 autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:0.5rem;">
          Masuk ✦
        </button>
      </form>

      <div class="auth-divider"><span>atau</span></div>

      <p class="auth-switch">
        Belum punya akun?
        <a href="<?= APP_URL ?>/auth/register" style="font-weight:600;">Daftar Sekarang</a>
      </p>

      <p style="text-align:center; margin-top:1rem;">
        <a href="<?= APP_URL ?>/" style="font-size:0.82rem; color:var(--ink-muted);">← Kembali ke Beranda</a>
      </p>

      <!-- Dev hint -->
      <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
      <div style="margin-top:2rem; padding:0.75rem; background:var(--paper-warm); border-radius:var(--r-md); font-size:0.78rem; color:var(--ink-muted);">
        <strong>Dev Hint:</strong> admin / <em>[lihat MD5 di database]</em>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
