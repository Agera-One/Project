<?php
// views/home.php
$pageTitle = APP_NAME . ' — Baca Artikel Terpercaya';
include dirname(__DIR__) . '/views/layouts/header.php';

// Icon mapping berdasarkan kata kunci judul
function getArticleIcon(string $title): string {
    $title = strtolower($title);
    if (str_contains($title, 'kesehatan') || str_contains($title, 'diet') || str_contains($title, 'obat') || str_contains($title, 'antibiotik')) return '💊';
    if (str_contains($title, 'mental')) return '🧠';
    if (str_contains($title, 'teknologi') || str_contains($title, 'ai')) return '💻';
    if (str_contains($title, 'lingkungan')) return '🌿';
    return '📖';
}
?>

<!-- ── HERO ──────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-eyebrow fade-up">
      <span>✦</span> Platform Artikel Terpercaya
    </div>
    <h1 class="hero-title fade-up fade-up-1">
      Temukan <em>Wawasan</em><br>yang Mengubah Perspektif
    </h1>
    <p class="hero-subtitle fade-up fade-up-2">
      Kumpulan artikel pilihan dari berbagai penulis berpengalaman. Informatif, terpercaya, dan mudah dipahami.
    </p>
    <div class="hero-actions fade-up fade-up-3">
      <a href="<?= APP_URL ?>/articles" class="btn btn-primary btn-lg">Jelajahi Artikel</a>
      <?php if (!isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/auth/register" class="btn btn-outline btn-lg">Bergabung Gratis</a>
      <?php endif; ?>
    </div>
    <div class="hero-stats fade-up fade-up-4">
      <div class="stat-item">
        <span class="stat-number"><?= $totalArticles ?>+</span>
        <span class="stat-label">Artikel</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">100%</span>
        <span class="stat-label">Gratis</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">Berbagai</span>
        <span class="stat-label">Topik</span>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURED ARTICLE ──────────────────────────────────── -->
<?php if ($featuredArticle): ?>
<section class="featured-section">
  <div class="container">
    <div class="section-label">✦ Artikel Terbaru</div>
    <a href="<?= APP_URL ?>/articles/<?= $featuredArticle['id'] ?>" style="text-decoration:none; color:inherit; display:block;">
      <div class="featured-card">
        <div class="featured-visual">
          <?= getArticleIcon($featuredArticle['title']) ?>
        </div>
        <div class="featured-content">
          <div class="featured-meta">
            <span class="featured-badge">Pilihan Editor</span>
            <span class="featured-date"><?= formatDate($featuredArticle['created_at']) ?></span>
          </div>
          <h2 class="featured-title"><?= e($featuredArticle['title']) ?></h2>
          <p class="featured-excerpt"><?= e(makeExcerpt($featuredArticle['content'], 220)) ?></p>
          <div class="featured-author">
            <div class="author-avatar"><?= strtoupper(substr($featuredArticle['author_name'] ?? 'A', 0, 1)) ?></div>
            <div>
              <div class="author-name"><?= e($featuredArticle['author_name'] ?? 'Tim Editorial') ?></div>
              <div class="author-source"><?= readingTime($featuredArticle['content']) ?></div>
            </div>
          </div>
          <span class="btn btn-primary">Baca Artikel ✦</span>
        </div>
      </div>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ── ARTICLE GRID ───────────────────────────────────────── -->
<?php if (!empty($allArticles)): ?>
<section class="articles-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Artikel Populer</h2>
      <a href="<?= APP_URL ?>/articles" class="btn btn-ghost">Lihat Semua →</a>
    </div>

    <div class="articles-grid">
      <?php foreach ($allArticles as $i => $art): ?>
      <article class="article-card observe-fade" style="animation-delay: <?= $i * 0.08 ?>s">
        <div class="card-visual">
          <?= getArticleIcon($art['title']) ?>
          <span class="card-visual-label">Artikel</span>
        </div>
        <div class="card-body">
          <div class="card-meta">
            <div class="card-author-chip">
              <div class="chip-avatar"><?= strtoupper(substr($art['author_name'] ?? 'A', 0, 1)) ?></div>
              <?= e(truncate($art['author_name'] ?? 'Anonim', 20)) ?>
            </div>
            <div class="card-dot"></div>
            <span class="card-date"><?= formatDate($art['created_at']) ?></span>
          </div>
          <h3 class="card-title">
            <a href="<?= APP_URL ?>/articles/<?= $art['id'] ?>"><?= e($art['title']) ?></a>
          </h3>
          <p class="card-excerpt"><?= e(makeExcerpt($art['content'], 140)) ?></p>
          <div class="card-footer">
            <a href="<?= APP_URL ?>/articles/<?= $art['id'] ?>" class="card-read-link">Baca selengkapnya</a>
            <span class="card-time-badge"><?= readingTime($art['content']) ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CTA SECTION ────────────────────────────────────────── -->
<?php if (!isLoggedIn()): ?>
<section style="padding:4rem 1.5rem; background: linear-gradient(135deg, var(--ink), #2d2520); text-align:center;">
  <div style="max-width:520px; margin:0 auto;">
    <p style="color:rgba(255,255,255,.5); font-size:0.78rem; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:1rem;">✦ Komunitas Pembaca</p>
    <h2 style="font-family:var(--serif); color:#fff; font-size:2rem; margin-bottom:1rem;">Mulai Perjalanan<br>Membacamu Hari Ini</h2>
    <p style="color:rgba(255,255,255,.6); margin-bottom:2rem;">Daftarkan diri sekarang dan nikmati akses penuh ke seluruh koleksi artikel kami. Gratis!</p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
      <a href="<?= APP_URL ?>/auth/register" class="btn btn-primary btn-lg">Daftar Sekarang</a>
      <a href="<?= APP_URL ?>/auth/login" style="padding:0.85rem 2rem; border-radius:999px; border:1.5px solid rgba(255,255,255,.3); color:rgba(255,255,255,.8); font-weight:600; font-size:1rem; text-decoration:none;">Sudah punya akun?</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include dirname(__DIR__) . '/views/layouts/footer.php'; ?>
