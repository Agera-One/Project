<?php
// views/admin/layout/header.php
$flash = getFlash();
$currentUser = currentUser();
$currentUri = $uri ?? '';

function adminLink(string $href, string $current): string {
    $active = str_starts_with($current, $href) ? 'active' : '';
    return $active;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin — ' . APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>

<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>" id="flashMsg">
  <span><?= $flash['message'] ?></span>
  <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
</div>
<?php endif; ?>

<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <div>
        <div style="display:flex; align-items:center; gap:0.5rem;">
          <span class="brand-icon">◈</span>
          <span class="brand-text"><?= APP_NAME ?></span>
        </div>
        <span class="sidebar-brand-sub">Admin Panel</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <p class="sidebar-section-label">Utama</p>
      <a href="<?= APP_URL ?>/admin/dashboard" class="sidebar-link <?= adminLink('/admin/dashboard', $currentUri) ?>">
        <span class="icon">⊞</span> Dashboard
      </a>

      <p class="sidebar-section-label">Konten</p>
      <a href="<?= APP_URL ?>/admin/articles" class="sidebar-link <?= adminLink('/admin/articles', $currentUri) ?>">
        <span class="icon">📄</span> Kelola Artikel
      </a>
      <a href="<?= APP_URL ?>/admin/articles/create" class="sidebar-link <?= ($currentUri === '/admin/articles/create') ? 'active' : '' ?>">
        <span class="icon">✚</span> Tulis Artikel
      </a>

      <p class="sidebar-section-label">Pengguna</p>
      <a href="<?= APP_URL ?>/admin/users" class="sidebar-link <?= adminLink('/admin/users', $currentUri) ?>">
        <span class="icon">👥</span> Daftar Pengguna
      </a>

      <p class="sidebar-section-label">Aksi</p>
      <a href="<?= APP_URL ?>/" class="sidebar-link">
        <span class="icon">🌐</span> Lihat Website
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($currentUser['username'], 0, 1)) ?></div>
        <div>
          <div class="sidebar-username"><?= e($currentUser['username']) ?></div>
          <div class="sidebar-role">Administrator</div>
        </div>
        <a href="<?= APP_URL ?>/auth/logout" class="sidebar-logout">↩</a>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
      <button class="topbar-hamburger" id="sidebarToggle" aria-label="Toggle Sidebar">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <div class="topbar-breadcrumb">
        <span>Admin</span>
        <span>›</span>
        <strong><?= e($breadcrumb ?? 'Dashboard') ?></strong>
      </div>

      <div class="topbar-actions">
        <a href="<?= APP_URL ?>/admin/articles/create" class="btn btn-primary btn-sm">+ Artikel Baru</a>
      </div>
    </header>

    <!-- Content Area -->
    <div class="admin-content">
