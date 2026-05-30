<?php
// views/articles/index.php
$pageTitle = 'Semua Artikel — ' . APP_NAME;
function getArticleIconSmall(string $title): string {
    $title = strtolower($title);
    if (str_contains($title, 'kesehatan') || str_contains($title, 'diet') || str_contains($title, 'antibiotik') || str_contains($title, 'obat')) return '💊';
    if (str_contains($title, 'mental')) return '🧠';
    if (str_contains($title, 'teknologi')) return '💻';
    return '📖';
}
include dirname(__DIR__, 2) . '/views/layouts/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="page-header-inner">
    <p class="page-eyebrow">✦ Perpustakaan Digital</p>
    <h1 class="page-title">Semua Artikel</h1>
    <p class="page-subtitle">Temukan artikel informatif dari berbagai penulis terpercaya.</p>

    <form class="search-bar" method="GET" action="<?= APP_URL ?>/articles">
      <input type="text" name="search" placeholder="Cari artikel, penulis..." value="<?= e($search) ?>" class="search-input">
      <button type="submit" class="btn btn-primary">Cari</button>
      <?php if ($search): ?>
        <a href="<?= APP_URL ?>/articles" class="btn btn-ghost">✕ Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Articles -->
<div class="articles-container">
  <?php if ($search): ?>
    <p class="results-count">
      Ditemukan <strong><?= $totalArticles ?> artikel</strong> untuk "<strong><?= e($search) ?></strong>"
    </p>
  <?php endif; ?>

  <?php if (empty($articles)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3 class="empty-title">Artikel Tidak Ditemukan</h3>
      <p class="empty-desc"><?= $search ? 'Coba kata kunci yang berbeda.' : 'Belum ada artikel yang dipublikasikan.' ?></p>
      <?php if ($search): ?>
        <a href="<?= APP_URL ?>/articles" class="btn btn-primary">Lihat Semua Artikel</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="articles-grid">
      <?php foreach ($articles as $i => $art): ?>
      <article class="article-card observe-fade" style="animation-delay:<?= $i * 0.07 ?>s">
        <div class="card-visual">
          <?= getArticleIconSmall($art['title']) ?>
          <span class="card-visual-label">Artikel</span>
        </div>
        <div class="card-body">
          <div class="card-meta">
            <div class="card-author-chip">
              <div class="chip-avatar"><?= strtoupper(substr($art['author_name'] ?? 'A', 0, 1)) ?></div>
              <?= e(truncate($art['author_name'] ?? 'Anonim', 22)) ?>
            </div>
            <div class="card-dot"></div>
            <span class="card-date"><?= formatDate($art['created_at']) ?></span>
          </div>
          <h3 class="card-title">
            <a href="<?= APP_URL ?>/articles/<?= $art['id'] ?>"><?= e($art['title']) ?></a>
          </h3>
          <p class="card-excerpt"><?= e(makeExcerpt($art['content'], 130)) ?></p>
          <div class="card-footer">
            <a href="<?= APP_URL ?>/articles/<?= $art['id'] ?>" class="card-read-link">Baca selengkapnya</a>
            <span class="card-time-badge"><?= readingTime($art['content']) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Navigasi halaman">
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
  <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/views/layouts/footer.php'; ?>
