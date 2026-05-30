<?php
// views/articles/show.php
$pageTitle = e($article['title']) . ' — ' . APP_NAME;
include dirname(__DIR__, 2) . '/views/layouts/header.php';
?>

<!-- Article Hero -->
<div class="article-hero">
  <div class="article-hero-inner">
    <nav class="article-breadcrumb">
      <a href="<?= APP_URL ?>/">Beranda</a>
      <span class="article-breadcrumb-sep">›</span>
      <a href="<?= APP_URL ?>/articles">Artikel</a>
      <span class="article-breadcrumb-sep">›</span>
      <span><?= e(truncate($article['title'], 40)) ?></span>
    </nav>

    <span class="article-category-badge">✦ Artikel</span>
    <h1 class="article-main-title"><?= e($article['title']) ?></h1>

    <div class="article-byline">
      <div class="byline-author">
        <div class="byline-avatar"><?= strtoupper(substr($article['author_name'] ?? 'A', 0, 1)) ?></div>
        <div>
          <div class="byline-name"><?= e($article['author_name'] ?? 'Tim Editorial') ?></div>
          <div class="byline-meta"><?= formatDate($article['created_at']) ?></div>
        </div>
      </div>
      <div class="byline-stats">
        <span class="byline-stat">⏱ <?= $readingTime ?></span>
        <?php if ($article['article_source']): ?>
          <a href="<?= e($article['article_source']) ?>" target="_blank" rel="noopener" class="byline-stat" style="color:var(--accent)">🔗 Sumber</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Article Body -->
<div class="article-body-wrap">
  <div class="article-body">
    <?php
    // Render konten: jika plain text, format menjadi paragraf HTML
    $content = $article['content'];
    // Cek apakah sudah mengandung HTML tag
    if (strip_tags($content) === $content) {
        // Plain text: konversi newline ke paragraf
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $content)));
        foreach ($paragraphs as $para) {
            // Cek apakah baris pendek (kemungkinan heading)
            $lines = array_filter(array_map('trim', explode("\n", $para)));
            if (count($lines) === 1 && strlen($para) < 80 && substr($para, -1) !== '.') {
                echo '<h2>' . e($para) . '</h2>';
            } else {
                echo '<p>' . nl2br(e($para)) . '</p>';
            }
        }
    } else {
        // Sudah HTML (dari rich text editor): tampilkan langsung
        // Gunakan htmlspecialchars_decode jika perlu, atau tampilkan apa adanya
        echo $content;
    }
    ?>
  </div>

  <?php if ($article['article_source']): ?>
  <div class="article-source-box">
    <span>📎 Sumber artikel: </span>
    <a href="<?= e($article['article_source']) ?>" target="_blank" rel="noopener noreferrer">
      <?= e($article['article_source']) ?>
    </a>
  </div>
  <?php endif; ?>

  <!-- Share / Navigation -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-top:2.5rem; padding-top:1.5rem; border-top:1px solid var(--cream);">
    <a href="<?= APP_URL ?>/articles" class="btn btn-ghost">← Kembali ke Daftar</a>
    <div style="display:flex; gap:0.5rem;">
      <?php if (isAdmin()): ?>
        <a href="<?= APP_URL ?>/admin/articles/<?= $article['id'] ?>/edit" class="btn btn-outline btn-sm">✏ Edit Artikel</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Related Articles -->
<?php if (!empty($related)): ?>
<section class="related-section">
  <div class="related-inner">
    <h3 class="related-title">Artikel Terkait</h3>
    <div class="related-grid">
      <?php foreach ($related as $rel): ?>
      <article class="related-card">
        <h4 class="related-card-title">
          <a href="<?= APP_URL ?>/articles/<?= $rel['id'] ?>"><?= e($rel['title']) ?></a>
        </h4>
        <p class="related-card-meta">
          <?= e($rel['author_name'] ?? 'Anonim') ?> · <?= formatDate($rel['created_at']) ?>
        </p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include dirname(__DIR__, 2) . '/views/layouts/footer.php'; ?>
