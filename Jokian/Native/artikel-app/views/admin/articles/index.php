<?php
// views/admin/articles/index.php
$pageTitle = 'Kelola Artikel — Admin';
$breadcrumb = 'Artikel';
include dirname(__DIR__, 3) . '/views/admin/layout/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
  <div>
    <h1 class="admin-page-title">Kelola Artikel</h1>
    <p class="admin-page-sub">Total <?= $total ?> artikel ditemukan.</p>
  </div>
  <a href="<?= APP_URL ?>/admin/articles/create" class="btn btn-primary">✚ Artikel Baru</a>
</div>

<!-- Search -->
<form class="admin-search-form" method="GET" action="<?= APP_URL ?>/admin/articles" style="margin-bottom:1.5rem;">
  <input type="text" name="search" placeholder="Cari judul atau penulis..." value="<?= e($search) ?>" class="admin-search-input">
  <button type="submit" class="btn btn-primary btn-sm">Cari</button>
  <?php if ($search): ?><a href="<?= APP_URL ?>/admin/articles" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
</form>

<div class="admin-table-card">
  <div class="table-card-header">
    <span class="table-card-title">Daftar Artikel</span>
    <?php if ($search): ?>
      <span style="font-size:0.8rem; color:var(--ink-muted);">Filter: "<?= e($search) ?>"</span>
    <?php endif; ?>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Judul & Sumber</th>
        <th>Penulis</th>
        <th>Tanggal</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($articles)): ?>
      <tr>
        <td colspan="5" style="text-align:center; padding:2.5rem; color:var(--ink-muted);">
          <?= $search ? 'Tidak ada artikel dengan kata kunci tersebut.' : 'Belum ada artikel.' ?>
        </td>
      </tr>
      <?php else: ?>
      <?php foreach ($articles as $i => $art): ?>
      <tr>
        <td style="color:var(--ink-muted);"><?= ($page - 1) * ADMIN_PER_PAGE + $i + 1 ?></td>
        <td class="title-cell" style="max-width:320px;">
          <strong><?= e(truncate($art['title'], 55)) ?></strong>
          <?php if ($art['article_source']): ?>
          <small>
            <a href="<?= e($art['article_source']) ?>" target="_blank" rel="noopener" style="color:var(--accent);">
              🔗 Sumber
            </a>
          </small>
          <?php endif; ?>
        </td>
        <td><?= e($art['author_name'] ?? '—') ?></td>
        <td style="white-space:nowrap;"><?= formatDate($art['created_at']) ?></td>
        <td>
          <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            <a href="<?= APP_URL ?>/articles/<?= $art['id'] ?>" target="_blank" class="btn btn-ghost btn-sm">👁 Lihat</a>
            <a href="<?= APP_URL ?>/admin/articles/<?= $art['id'] ?>/edit" class="btn btn-outline btn-sm">✏ Edit</a>
            <form method="POST" action="<?= APP_URL ?>/admin/articles/<?= $art['id'] ?>/delete"
                  data-confirm="Yakin ingin menghapus artikel '<?= e(addslashes($art['title'])) ?>'? Tindakan ini tidak bisa dibatalkan.">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="page-btn">‹</a>
  <?php endif; ?>
  <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
    <a href="?page=<?= $p ?><?= $search ? '&search='.urlencode($search) : '' ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="page-btn">›</a>
  <?php endif; ?>
</nav>
<?php endif; ?>

<?php include dirname(__DIR__, 3) . '/views/admin/layout/footer.php'; ?>
