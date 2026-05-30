<?php // views/layouts/footer.php ?>
</main>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="brand-icon">◈</span>
      <span class="brand-text"><?= APP_NAME ?></span>
      <p class="footer-tagline">Pengetahuan yang terpercaya, disajikan dengan elegan.</p>
    </div>
    <div class="footer-links">
      <a href="<?= APP_URL ?>/">Beranda</a>
      <a href="<?= APP_URL ?>/articles">Semua Artikel</a>
      <?php if (!isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/auth/login">Masuk</a>
        <a href="<?= APP_URL ?>/auth/register">Daftar</a>
      <?php endif; ?>
    </div>
    <div class="footer-copy">
      &copy; <?= date('Y') ?> <?= APP_NAME ?>. Dibuat dengan ♥ menggunakan PHP Native.
    </div>
  </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
