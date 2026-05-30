<?php
// views/auth/register.php
$pageTitle = 'Daftar — ' . APP_NAME;
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
      <blockquote>"Ilmu yang bermanfaat adalah investasi terbaik sepanjang masa."</blockquote>
      <cite>— Kutipan Inspiratif</cite>
    </div>
    <p class="auth-tagline">Bergabung dengan komunitas pembaca kami</p>
  </div>

  <!-- Form Side -->
  <div class="auth-form-side">
    <div class="auth-form-inner fade-up">
      <div class="auth-form-header">
        <h1 class="auth-form-title">Buat Akun Baru</h1>
        <p class="auth-form-subtitle">Gratis selamanya. Mulai membaca artikel berkualitas hari ini.</p>
      </div>

      <form method="POST" action="<?= APP_URL ?>/auth/register">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" name="username"
                 class="form-control"
                 placeholder="Pilih username unikmu"
                 value="<?= e(post('username')) ?>"
                 autocomplete="username"
                 pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="50" required>
          <small style="color:var(--ink-muted); font-size:0.78rem; margin-top:0.3rem; display:block;">
            Minimal 3 karakter, hanya huruf, angka, dan underscore.
          </small>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password"
                 class="form-control"
                 placeholder="Minimal 8 karakter"
                 minlength="8" autocomplete="new-password" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="confirm_password">Konfirmasi Password</label>
          <input type="password" id="confirm_password" name="confirm_password"
                 class="form-control"
                 placeholder="Ulangi password kamu"
                 minlength="8" autocomplete="new-password" required>
        </div>

        <!-- Password strength visual -->
        <div id="strengthBar" style="height:4px; border-radius:2px; background:var(--cream); margin-bottom:1rem; overflow:hidden;">
          <div id="strengthFill" style="height:100%; width:0; border-radius:2px; transition:all 0.3s; background:var(--accent);"></div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
          Daftar Sekarang ✦
        </button>
      </form>

      <div class="auth-divider"><span>atau</span></div>

      <p class="auth-switch">
        Sudah punya akun?
        <a href="<?= APP_URL ?>/auth/login" style="font-weight:600;">Masuk di sini</a>
      </p>
      <p style="text-align:center; margin-top:0.75rem;">
        <a href="<?= APP_URL ?>/" style="font-size:0.82rem; color:var(--ink-muted);">← Kembali ke Beranda</a>
      </p>

      <!-- Password security notice -->
      <div style="margin-top:1.75rem; padding:0.9rem 1rem; background:var(--accent-pale); border-radius:var(--r-md); font-size:0.78rem; color:var(--accent);">
        🔒 <strong>Keamanan:</strong> Password disimpan menggunakan enkripsi <strong>bcrypt</strong> yang aman. Data kamu terlindungi.
      </div>
    </div>
  </div>
</div>

<script>
// Password strength indicator
const pw = document.getElementById('password');
const fill = document.getElementById('strengthFill');
const colors = ['#ef4444','#f97316','#eab308','#22c55e'];

pw?.addEventListener('input', () => {
  const v = pw.value;
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  fill.style.width = (score * 25) + '%';
  fill.style.background = colors[score - 1] || 'var(--cream)';
});
</script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
