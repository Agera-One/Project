<?php
// views/admin/articles/form.php
$isEdit     = isset($article);
$pageTitle  = ($isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru') . ' — Admin';
$breadcrumb = $isEdit ? 'Edit Artikel' : 'Tulis Artikel';
$formAction = $isEdit
    ? APP_URL . '/admin/articles/' . $article['id'] . '/update'
    : APP_URL . '/admin/articles/store';

include dirname(__DIR__, 3) . '/views/admin/layout/header.php';
?>

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.5rem;">
  <a href="<?= APP_URL ?>/admin/articles" style="color:var(--ink-muted); font-size:0.88rem;">← Kembali</a>
  <span style="color:var(--cream);">|</span>
  <h1 class="admin-page-title" style="margin:0;"><?= $isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru' ?></h1>
</div>
<p class="admin-page-sub"><?= $isEdit ? 'Perbarui informasi dan konten artikel.' : 'Isi semua field di bawah untuk menerbitkan artikel baru.' ?></p>

<form method="POST" action="<?= $formAction ?>" id="articleForm">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

  <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; align-items:start;">

    <!-- Main Column -->
    <div>
      <!-- Judul -->
      <div class="admin-form-card" style="margin-bottom:1.5rem;">
        <p class="form-section-title">Informasi Artikel</p>

        <div class="form-group">
          <label class="form-label" for="title">Judul Artikel <span style="color:var(--accent)">*</span></label>
          <input type="text" id="title" name="title" class="form-control"
                 placeholder="Tuliskan judul artikel yang menarik..."
                 value="<?= e($article['title'] ?? post('title')) ?>"
                 maxlength="200" required>
          <small id="titleCount" style="font-size:0.75rem; color:var(--ink-muted); display:block; margin-top:0.3rem;">
            0 / 200 karakter
          </small>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="author_name">Nama Penulis <span style="color:var(--accent)">*</span></label>
            <input type="text" id="author_name" name="author_name" class="form-control"
                   placeholder="Nama lengkap penulis"
                   value="<?= e($article['author_name'] ?? post('author_name')) ?>"
                   maxlength="100" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="article_source">URL Sumber Artikel</label>
            <input type="url" id="article_source" name="article_source" class="form-control"
                   placeholder="https://contoh.com/artikel"
                   value="<?= e($article['article_source'] ?? post('article_source')) ?>">
          </div>
        </div>
      </div>

      <!-- Rich Text Editor -->
      <div class="admin-form-card">
        <p class="form-section-title">Konten Artikel <span style="color:var(--accent)">*</span></p>

        <!-- Toolbar -->
        <div class="rte-toolbar" id="rteToolbar">
          <div class="rte-toolbar-group">
            <button type="button" class="rte-btn" data-cmd="bold" title="Bold (Ctrl+B)"><b>B</b></button>
            <button type="button" class="rte-btn" data-cmd="italic" title="Italic (Ctrl+I)"><i>I</i></button>
            <button type="button" class="rte-btn" data-cmd="underline" title="Underline (Ctrl+U)"><u>U</u></button>
            <button type="button" class="rte-btn" data-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
          </div>
          <div class="rte-divider"></div>
          <div class="rte-toolbar-group">
            <select class="rte-select" id="headingSelect" title="Format Teks">
              <option value="">Format Normal</option>
              <option value="H2">Heading 2</option>
              <option value="H3">Heading 3</option>
              <option value="H4">Heading 4</option>
            </select>
          </div>
          <div class="rte-divider"></div>
          <div class="rte-toolbar-group">
            <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullet List">☰</button>
            <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbered List">①</button>
            <button type="button" class="rte-btn" data-cmd="formatBlock" data-value="blockquote" title="Kutipan">❝</button>
          </div>
          <div class="rte-divider"></div>
          <div class="rte-toolbar-group">
            <button type="button" class="rte-btn" data-cmd="justifyLeft" title="Rata Kiri">⬡L</button>
            <button type="button" class="rte-btn" data-cmd="justifyCenter" title="Tengah">⬡C</button>
            <button type="button" class="rte-btn" data-cmd="justifyRight" title="Rata Kanan">⬡R</button>
          </div>
          <div class="rte-divider"></div>
          <div class="rte-toolbar-group">
            <button type="button" class="rte-btn" id="linkBtn" title="Insert Link">🔗</button>
            <button type="button" class="rte-btn" data-cmd="removeFormat" title="Hapus Format">✕</button>
          </div>
          <div class="rte-toolbar-group" style="margin-left:auto;">
            <button type="button" class="rte-btn" id="previewToggle" title="Toggle Preview">👁 Preview</button>
          </div>
        </div>

        <!-- Editable Area -->
        <div id="rteEditor" class="rte-editor" contenteditable="true" spellcheck="true"><?php
          if ($isEdit) {
            $c = $article['content'];
            // Jika plain text, konversi ke HTML
            if (strip_tags($c) === $c) {
                $paragraphs = array_filter(array_map('trim', explode("\n\n", $c)));
                foreach ($paragraphs as $para) {
                    $lines = array_filter(array_map('trim', explode("\n", $para)));
                    if (count($lines) === 1 && strlen($para) < 80 && substr($para, -1) !== '.') {
                        echo '<h2>' . htmlspecialchars($para, ENT_QUOTES) . '</h2>';
                    } else {
                        echo '<p>' . nl2br(htmlspecialchars($para, ENT_QUOTES)) . '</p>';
                    }
                }
            } else { echo $c; }
          } else {
            echo '<p><br></p>';
          }
        ?></div>

        <!-- Preview Area (hidden) -->
        <div id="rtePreview" class="rte-preview" style="display:none;"></div>

        <!-- Word Count -->
        <div class="rte-footer">
          <span id="wordCount">0 kata</span>
          <span id="readTimeEst">~0 menit baca</span>
        </div>

        <!-- Hidden textarea untuk submit -->
        <textarea name="content" id="contentInput" style="display:none;"></textarea>
      </div>
    </div>

    <!-- Sidebar Column -->
    <div>
      <!-- Publish Card -->
      <div class="admin-form-card" style="margin-bottom:1.25rem;">
        <p class="form-section-title">Terbitkan</p>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
          <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
            <?= $isEdit ? '💾 Simpan Perubahan' : '🚀 Terbitkan Artikel' ?>
          </button>
          <a href="<?= APP_URL ?>/admin/articles" class="btn btn-ghost" style="width:100%; justify-content:center; text-align:center;">Batal</a>
          <?php if ($isEdit): ?>
          <hr style="border:none; border-top:1px solid var(--cream); margin:0.25rem 0;">
          <a href="<?= APP_URL ?>/articles/<?= $article['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="width:100%; justify-content:center;">👁 Lihat Artikel</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Meta Info -->
      <?php if ($isEdit): ?>
      <div class="admin-form-card" style="margin-bottom:1.25rem;">
        <p class="form-section-title">Informasi</p>
        <div style="font-size:0.82rem; color:var(--ink-muted); display:flex; flex-direction:column; gap:0.5rem;">
          <div><strong>ID Artikel:</strong> #<?= $article['id'] ?></div>
          <div><strong>Dibuat:</strong> <?= formatDate($article['created_at']) ?></div>
          <div><strong>Penulis DB:</strong> <?= e($article['author_username'] ?? '—') ?></div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Writing Tips -->
      <div class="admin-form-card">
        <p class="form-section-title">💡 Tips Menulis</p>
        <ul style="font-size:0.8rem; color:var(--ink-muted); padding-left:1rem; display:flex; flex-direction:column; gap:0.4rem;">
          <li>Buat judul yang spesifik dan informatif</li>
          <li>Gunakan paragraf pendek (3-5 kalimat)</li>
          <li>Tambahkan heading untuk struktur yang jelas</li>
          <li>Sertakan sumber artikel agar terpercaya</li>
          <li>Minimal 300 kata untuk artikel berkualitas</li>
        </ul>
      </div>
    </div>
  </div>
</form>

<!-- RTE Styles -->
<style>
.rte-toolbar {
  display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap;
  padding: 0.6rem 0.75rem; background: #faf8f4;
  border: 1.5px solid var(--cream); border-radius: var(--r-md) var(--r-md) 0 0;
  border-bottom: none;
}
.rte-toolbar-group { display: flex; gap: 0.15rem; }
.rte-divider { width: 1px; height: 20px; background: var(--cream); margin: 0 0.25rem; }
.rte-btn {
  padding: 0.3rem 0.55rem; border-radius: 4px; border: 1px solid transparent;
  background: none; cursor: pointer; font-size: 0.82rem; color: var(--ink-soft);
  transition: all 0.15s; font-family: var(--sans); line-height: 1;
  min-width: 30px; display: flex; align-items: center; justify-content: center;
}
.rte-btn:hover { background: var(--cream); border-color: #ddd; color: var(--ink); }
.rte-btn.active { background: var(--accent-pale); border-color: var(--accent); color: var(--accent); }
.rte-select {
  padding: 0.28rem 0.5rem; border-radius: 4px; border: 1px solid var(--cream);
  font-size: 0.8rem; font-family: var(--sans); background: #fff; color: var(--ink-soft);
  cursor: pointer;
}
.rte-editor {
  min-height: 420px; max-height: 600px; overflow-y: auto;
  padding: 1.25rem 1.5rem;
  border: 1.5px solid var(--cream); border-radius: 0 0 var(--r-md) var(--r-md);
  font-family: var(--sans); font-size: 1rem; color: var(--ink-soft); line-height: 1.8;
  outline: none; transition: border-color var(--transition);
}
.rte-editor:focus { border-color: var(--accent); }
.rte-editor h2 { font-family: var(--serif); font-size: 1.5rem; margin: 1.5rem 0 0.6rem; color: var(--ink); }
.rte-editor h3 { font-family: var(--serif); font-size: 1.25rem; margin: 1.25rem 0 0.5rem; color: var(--ink); }
.rte-editor h4 { font-size: 1.1rem; margin: 1rem 0 0.4rem; font-weight: 600; color: var(--ink); }
.rte-editor p { margin-bottom: 0.9rem; }
.rte-editor blockquote { border-left: 3px solid var(--accent); padding: 0.75rem 1.25rem; background: var(--accent-pale); margin: 1rem 0; border-radius: 0 var(--r-sm) var(--r-sm) 0; font-style: italic; }
.rte-editor ul, .rte-editor ol { margin: 0.75rem 0 0.75rem 1.5rem; }
.rte-editor li { margin-bottom: 0.25rem; }
.rte-editor a { color: var(--accent); }
.rte-editor:empty::before { content: 'Mulai menulis konten artikel di sini...'; color: var(--ink-muted); pointer-events: none; }
.rte-preview {
  min-height: 420px; padding: 1.25rem 1.5rem;
  border: 1.5px dashed var(--cream); border-radius: 0 0 var(--r-md) var(--r-md);
  font-size: 1rem; line-height: 1.8; color: var(--ink-soft);
  background: var(--paper-warm);
}
.rte-footer {
  display: flex; justify-content: space-between;
  padding: 0.5rem 0.75rem; font-size: 0.75rem; color: var(--ink-muted);
  background: #faf8f4; border: 1.5px solid var(--cream);
  border-top: none; border-radius: 0 0 var(--r-md) var(--r-md);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const editor     = document.getElementById('rteEditor');
  const input      = document.getElementById('contentInput');
  const form       = document.getElementById('articleForm');
  const wordCount  = document.getElementById('wordCount');
  const readEst    = document.getElementById('readTimeEst');
  const titleInput = document.getElementById('title');
  const titleCount = document.getElementById('titleCount');
  const preview    = document.getElementById('rtePreview');
  const previewBtn = document.getElementById('previewToggle');
  let isPreviewing = false;

  // Sync content on form submit
  // PENTING: isi textarea DULU sebelum validasi apapun
  form.addEventListener('submit', (e) => {
    const htmlContent = editor.innerHTML;
    const textContent = editor.innerText.trim();

    // Selalu sync ke textarea terlebih dahulu
    input.value = htmlContent;
    input.removeAttribute('required'); // hapus required agar browser tidak blokir

    // Baru validasi manual
    if (!textContent || textContent === '\n') {
      e.preventDefault();
      editor.focus();
      editor.style.borderColor = 'var(--accent)';
      editor.style.boxShadow = '0 0 0 3px rgba(200,90,46,.2)';
      alert('Konten artikel wajib diisi!');
      return;
    }
  });

  // Toolbar buttons
  document.querySelectorAll('.rte-btn[data-cmd]').forEach(btn => {
    btn.addEventListener('mousedown', (e) => {
      e.preventDefault();
      const cmd   = btn.dataset.cmd;
      const value = btn.dataset.value || null;
      document.execCommand(cmd, false, value);
      editor.focus();
      updateButtonStates();
    });
  });

  // Heading select
  document.getElementById('headingSelect').addEventListener('change', function () {
    if (this.value) {
      document.execCommand('formatBlock', false, this.value);
    } else {
      document.execCommand('formatBlock', false, 'p');
    }
    editor.focus();
    this.value = '';
  });

  // Link button
  document.getElementById('linkBtn').addEventListener('click', () => {
    const url = prompt('Masukkan URL tautan:', 'https://');
    if (url) document.execCommand('createLink', false, url);
    editor.focus();
  });

  // Preview toggle
  previewBtn.addEventListener('click', () => {
    isPreviewing = !isPreviewing;
    if (isPreviewing) {
      preview.innerHTML = editor.innerHTML;
      preview.style.display = 'block';
      editor.style.display  = 'none';
      previewBtn.textContent = '✏ Edit';
      previewBtn.classList.add('active');
    } else {
      preview.style.display = 'none';
      editor.style.display  = 'block';
      previewBtn.textContent = '👁 Preview';
      previewBtn.classList.remove('active');
    }
  });

  // Word count & read time
  function updateStats() {
    const text  = editor.innerText.trim();
    const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
    const mins  = Math.max(1, Math.ceil(words / 200));
    wordCount.textContent = words + ' kata';
    readEst.textContent   = '~' + mins + ' menit baca';
  }
  editor.addEventListener('input', updateStats);
  updateStats();

  // Title character count
  function updateTitleCount() {
    const len = titleInput.value.length;
    titleCount.textContent = len + ' / 200 karakter';
    titleCount.style.color = len > 180 ? 'var(--accent)' : 'var(--ink-muted)';
  }
  titleInput.addEventListener('input', updateTitleCount);
  updateTitleCount();

  // Button active states
  function updateButtonStates() {
    document.querySelectorAll('.rte-btn[data-cmd]').forEach(btn => {
      try {
        btn.classList.toggle('active', document.queryCommandState(btn.dataset.cmd));
      } catch (_) {}
    });
  }
  editor.addEventListener('keyup', updateButtonStates);
  editor.addEventListener('mouseup', updateButtonStates);
  document.addEventListener('selectionchange', updateButtonStates);

  // Paste as plain text (strip external formatting)
  editor.addEventListener('paste', (e) => {
    e.preventDefault();
    const text = e.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
  });

  // Tab key inserts spaces
  editor.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      e.preventDefault();
      document.execCommand('insertText', false, '    ');
    }
  });
});
</script>

<?php include dirname(__DIR__, 3) . '/views/admin/layout/footer.php'; ?>
