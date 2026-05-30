<?php
// views/layouts/header.php
// Dipanggil di awal setiap view publik
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
</head>
<body>

<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a href="<?= APP_URL ?>/" class="nav-brand">
      <span class="brand-icon">◈</span>
      <span class="brand-text"><?= APP_NAME ?></span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-links" id="navLinks">
      <a href="<?= APP_URL ?>/" class="nav-link <?= ($uri === '/') ? 'active' : '' ?>">Beranda</a>
      <a href="<?= APP_URL ?>/articles" class="nav-link <?= str_starts_with($uri, '/articles') ? 'active' : '' ?>">Artikel</a>

      <?php if (isLoggedIn()): ?>
        <?php if (isAdmin()): ?>
          <a href="<?= APP_URL ?>/admin/dashboard" class="nav-link nav-cta">Dashboard Admin</a>
        <?php endif; ?>
        <div class="nav-user">
          <span class="nav-username">@<?= e(currentUser()['username']) ?></span>
          <a href="<?= APP_URL ?>/auth/logout" class="nav-logout">Keluar</a>
        </div>
      <?php else: ?>
        <a href="<?= APP_URL ?>/auth/login" class="nav-link">Masuk</a>
        <a href="<?= APP_URL ?>/auth/register" class="btn-nav-register">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>" id="flashMsg">
  <span><?= $flash['message'] ?></span>
  <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
</div>
<?php endif; ?>

<main class="main-content">
