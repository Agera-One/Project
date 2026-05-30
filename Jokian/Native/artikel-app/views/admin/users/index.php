<?php
// views/admin/users/index.php
$pageTitle  = 'Kelola Pengguna — Admin';
$breadcrumb = 'Pengguna';
include dirname(__DIR__, 3) . '/views/admin/layout/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
  <div>
    <h1 class="admin-page-title">Kelola Pengguna</h1>
    <p class="admin-page-sub">Total <?= $total ?> pengguna terdaftar di sistem.</p>
  </div>
</div>

<div class="admin-table-card">
  <div class="table-card-header">
    <span class="table-card-title">Daftar Pengguna</span>
    <span style="font-size:0.78rem; color:var(--ink-muted);">Kamu tidak bisa menghapus atau menurunkan role akunmu sendiri.</span>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Username</th>
        <th>Role</th>
        <th>Terdaftar</th>
        <th>Ubah Role</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($users)): ?>
      <tr>
        <td colspan="6" style="text-align:center; padding:2.5rem; color:var(--ink-muted);">Belum ada pengguna.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($users as $i => $u): ?>
      <?php $isSelf = $u['id'] === currentUserId(); ?>
      <tr <?= $isSelf ? 'style="background:rgba(200,90,46,.04);"' : '' ?>>
        <td style="color:var(--ink-muted);"><?= ($page - 1) * ADMIN_PER_PAGE + $i + 1 ?></td>
        <td>
          <div style="display:flex; align-items:center; gap:0.6rem;">
            <div class="author-avatar" style="width:32px; height:32px; font-size:0.78rem;">
              <?= strtoupper(substr($u['username'], 0, 1)) ?>
            </div>
            <div>
              <strong><?= e($u['username']) ?></strong>
              <?php if ($isSelf): ?><span style="font-size:0.72rem; color:var(--accent); margin-left:0.4rem;">(Kamu)</span><?php endif; ?>
            </div>
          </div>
        </td>
        <td>
          <span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
        </td>
        <td style="white-space:nowrap; color:var(--ink-muted);"><?= formatDate($u['created_at']) ?></td>
        <td>
          <?php if (!$isSelf): ?>
          <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/role"
                style="display:flex; gap:0.4rem; align-items:center;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <select name="role" class="rte-select" style="font-size:0.8rem;">
              <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>User</option>
              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <button type="submit" class="btn btn-ghost btn-sm">Simpan</button>
          </form>
          <?php else: ?>
          <span style="font-size:0.8rem; color:var(--ink-muted);">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if (!$isSelf): ?>
          <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/delete"
                data-confirm="Yakin ingin menghapus pengguna '<?= e(addslashes($u['username'])) ?>'?\n\nSemua artikel milik pengguna ini juga akan ikut terhapus (CASCADE).">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
          </form>
          <?php else: ?>
          <span style="font-size:0.8rem; color:var(--ink-muted);">Tidak bisa dihapus</span>
          <?php endif; ?>
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
    <a href="?page=<?= $page-1 ?>" class="page-btn">‹</a>
  <?php endif; ?>
  <?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
    <a href="?page=<?= $p ?>" class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?>" class="page-btn">›</a>
  <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Info Box -->
<div style="margin-top:1.5rem; padding:1rem 1.25rem; background:var(--accent-pale); border-radius:var(--r-md); font-size:0.82rem; color:var(--accent); border:1px solid rgba(200,90,46,.2);">
  <strong>⚠ Perhatian:</strong> Menghapus pengguna akan menghapus semua artikel yang dimilikinya secara otomatis (ON DELETE CASCADE). Tindakan ini tidak dapat dibatalkan.
</div>

<?php include dirname(__DIR__, 3) . '/views/admin/layout/footer.php'; ?>
