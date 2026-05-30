<?php
// views/errors/403.php
$pageTitle = '403 — Akses Ditolak';
include dirname(__DIR__, 2) . '/views/layouts/header.php';
?>
<div style="min-height:60vh; display:flex; align-items:center; justify-content:center; text-align:center; padding:3rem 1.5rem;">
  <div>
    <div style="font-family:var(--serif); font-size:clamp(5rem,15vw,10rem); color:var(--accent); opacity:0.15; line-height:1;">403</div>
    <h1 style="font-size:1.75rem; margin-bottom:0.75rem; margin-top:-1rem;">Akses Ditolak</h1>
    <p style="color:var(--ink-muted); margin-bottom:2rem; max-width:380px; margin-left:auto; margin-right:auto;">
      Kamu tidak memiliki izin untuk mengakses halaman ini. Hanya administrator yang dapat masuk ke area ini.
    </p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
      <a href="<?= APP_URL ?>/" class="btn btn-primary">← Beranda</a>
      <?php if (!isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/auth/login" class="btn btn-outline">Masuk sebagai Admin</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include dirname(__DIR__, 2) . '/views/layouts/footer.php'; ?>
