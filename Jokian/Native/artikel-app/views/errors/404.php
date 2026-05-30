<?php
// views/errors/404.php
$pageTitle = '404 — Halaman Tidak Ditemukan';
include dirname(__DIR__, 2) . '/views/layouts/header.php';
?>
<div style="min-height:60vh; display:flex; align-items:center; justify-content:center; text-align:center; padding:3rem 1.5rem;">
  <div>
    <div style="font-family:var(--serif); font-size:clamp(5rem,15vw,10rem); color:var(--accent); opacity:0.15; line-height:1;">404</div>
    <h1 style="font-size:1.75rem; margin-bottom:0.75rem; margin-top:-1rem;">Halaman Tidak Ditemukan</h1>
    <p style="color:var(--ink-muted); margin-bottom:2rem; max-width:380px; margin-left:auto; margin-right:auto;">
      Halaman yang kamu cari tidak ada atau telah dipindahkan. Coba kembali ke beranda.
    </p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
      <a href="<?= APP_URL ?>/" class="btn btn-primary">← Kembali ke Beranda</a>
      <a href="<?= APP_URL ?>/articles" class="btn btn-ghost">Jelajahi Artikel</a>
    </div>
  </div>
</div>
<?php include dirname(__DIR__, 2) . '/views/layouts/footer.php'; ?>
