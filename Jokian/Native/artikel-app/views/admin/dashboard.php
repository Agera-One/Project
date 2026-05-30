<?php
// views/admin/dashboard.php
$pageTitle = 'Dashboard — Admin ' . APP_NAME;
$breadcrumb = 'Dashboard';
include dirname(__DIR__, 2) . '/views/admin/layout/header.php';

// Hitung max untuk chart bar normalisasi
$maxMonthly = max(array_column($monthlyStats, 'total') ?: [1]);
?>

<h1 class="admin-page-title">Dashboard</h1>
<p class="admin-page-sub">Selamat datang kembali, <strong><?= e(currentUser()['username']) ?></strong>. Berikut ringkasan data aplikasimu.</p>

<!-- Stat Cards -->
<div class="stats-grid">
  <div class="stat-card accent-red fade-up fade-up-1">
    <div class="stat-card-icon">📄</div>
    <div class="stat-card-value"><?= number_format($totalArticles) ?></div>
    <div class="stat-card-label">Total Artikel</div>
  </div>
  <div class="stat-card accent-green fade-up fade-up-2">
    <div class="stat-card-icon">👥</div>
    <div class="stat-card-value"><?= number_format($totalUsers) ?></div>
    <div class="stat-card-label">Total Pengguna</div>
  </div>
  <div class="stat-card accent-gold fade-up fade-up-3">
    <div class="stat-card-icon">✍️</div>
    <div class="stat-card-value"><?= count($monthlyStats) ?></div>
    <div class="stat-card-label">Bulan Aktif</div>
  </div>
  <div class="stat-card accent-blue fade-up fade-up-4">
    <div class="stat-card-icon">🔒</div>
    <div class="stat-card-value">bcrypt</div>
    <div class="stat-card-label">Enkripsi Password</div>
  </div>
</div>

<!-- Chart + Latest -->
<div style="display:grid; grid-template-columns:1fr 2fr; gap:1.5rem; margin-bottom:1.75rem;">
  <!-- Monthly Chart -->
  <div class="admin-table-card">
    <div class="table-card-header">
      <span class="table-card-title">Artikel per Bulan</span>
    </div>
    <div style="padding:1.5rem;">
      <?php if (!empty($monthlyStats)): ?>
      <div class="chart-bar-group" style="padding-bottom:1.5rem;">
        <?php foreach ($monthlyStats as $stat): ?>
          <?php $h = max(10, round(($stat['total'] / $maxMonthly) * 80)); ?>
          <div class="chart-bar" style="height:<?= $h ?>px;" data-label="<?= e($stat['month']) ?>" title="<?= $stat['total'] ?> artikel"></div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:0.75rem;">
        <?php foreach ($monthlyStats as $stat): ?>
          <div style="display:flex; justify-content:space-between; font-size:0.78rem; color:var(--ink-muted); margin-bottom:0.25rem;">
            <span><?= e($stat['month']) ?></span>
            <strong style="color:var(--ink);"><?= $stat['total'] ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="color:var(--ink-muted); font-size:0.88rem; text-align:center; padding:2rem 0;">Belum ada data.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Latest Articles -->
  <div class="admin-table-card">
    <div class="table-card-header">
      <span class="table-card-title">Artikel Terbaru</span>
      <a href="<?= APP_URL ?>/admin/articles" class="btn btn-ghost btn-sm">Lihat Semua</a>
    </div>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Judul</th>
          <th>Penulis</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latestArticles as $art): ?>
        <tr>
          <td class="title-cell">
            <strong><?= e(truncate($art['title'], 45)) ?></strong>
          </td>
          <td><?= e($art['author_name'] ?? '-') ?></td>
          <td><?= formatDate($art['created_at']) ?></td>
          <td>
            <a href="<?= APP_URL ?>/admin/articles/<?= $art['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($latestArticles)): ?>
        <tr><td colspan="4" style="text-align:center; color:var(--ink-muted); padding:2rem;">Belum ada artikel.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick Actions -->
<div style="display:flex; gap:1rem; flex-wrap:wrap;">
  <a href="<?= APP_URL ?>/admin/articles/create" class="btn btn-primary">✚ Tulis Artikel Baru</a>
  <a href="<?= APP_URL ?>/admin/articles" class="btn btn-outline">📄 Kelola Artikel</a>
  <a href="<?= APP_URL ?>/admin/users" class="btn btn-ghost">👥 Kelola Pengguna</a>
  <a href="<?= APP_URL ?>/" target="_blank" class="btn btn-ghost">🌐 Lihat Website</a>
</div>

<?php include dirname(__DIR__, 2) . '/views/admin/layout/footer.php'; ?>
