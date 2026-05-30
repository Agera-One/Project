<?php
// app/controllers/AdminController.php
// ============================================================
// Controller Admin — CRUD Artikel & Manajemen User
// ============================================================

class AdminController {
    private Article $articleModel;
    private User $userModel;

    public function __construct() {
        $this->articleModel = new Article();
        $this->userModel    = new User();
        // Semua method admin wajib admin
        AuthMiddleware::requireAdmin();
    }

    // ---- GET /admin/dashboard ----
    public function dashboard(): void {
        $totalArticles = $this->articleModel->countAll();
        $totalUsers    = $this->userModel->countAll();
        $latestArticles = $this->articleModel->getAll(1, 5);
        $monthlyStats  = $this->articleModel->getMonthlyStats();

        include dirname(__DIR__, 2) . '/views/admin/dashboard.php';
    }

    // ====================================================
    // ARTIKEL
    // ====================================================

    // ---- GET /admin/articles ----
    public function articles(): void {
        $page   = max(1, (int) get('page', 1));
        $search = trim(get('search', ''));

        $articles    = $this->articleModel->getAll($page, ADMIN_PER_PAGE, $search);
        $total       = $this->articleModel->countAll($search);
        $totalPages  = (int) ceil($total / ADMIN_PER_PAGE);

        include dirname(__DIR__, 2) . '/views/admin/articles/index.php';
    }

    // ---- GET /admin/articles/create ----
    public function createArticle(): void {
        include dirname(__DIR__, 2) . '/views/admin/articles/form.php';
    }

    // ---- POST /admin/articles/store ----
    public function storeArticle(): void {
        verifyCsrf();

        $data = $this->validateArticleInput();
        if (!$data) redirect('/admin/articles/create');

        $data['author_id'] = currentUserId();

        $id = $this->articleModel->create($data);
        if ($id) {
            flash('success', 'Artikel berhasil ditambahkan!');
            redirect('/admin/articles');
        } else {
            flash('error', 'Gagal menyimpan artikel. Coba lagi.');
            redirect('/admin/articles/create');
        }
    }

    // ---- GET /admin/articles/{id}/edit ----
    public function editArticle(int $id): void {
        $article = $this->articleModel->findById($id);
        if (!$article) {
            flash('error', 'Artikel tidak ditemukan.');
            redirect('/admin/articles');
        }
        include dirname(__DIR__, 2) . '/views/admin/articles/form.php';
    }

    // ---- POST /admin/articles/{id}/update ----
    public function updateArticle(int $id): void {
        verifyCsrf();

        $article = $this->articleModel->findById($id);
        if (!$article) {
            flash('error', 'Artikel tidak ditemukan.');
            redirect('/admin/articles');
        }

        $data = $this->validateArticleInput();
        if (!$data) redirect("/admin/articles/$id/edit");

        if ($this->articleModel->update($id, $data)) {
            flash('success', 'Artikel berhasil diperbarui!');
            redirect('/admin/articles');
        } else {
            flash('error', 'Gagal memperbarui artikel.');
            redirect("/admin/articles/$id/edit");
        }
    }

    // ---- POST /admin/articles/{id}/delete ----
    public function deleteArticle(int $id): void {
        verifyCsrf();

        if ($this->articleModel->delete($id)) {
            flash('success', 'Artikel berhasil dihapus.');
        } else {
            flash('error', 'Gagal menghapus artikel.');
        }
        redirect('/admin/articles');
    }

    // ====================================================
    // USER MANAGEMENT
    // ====================================================

    // ---- GET /admin/users ----
    public function users(): void {
        $page  = max(1, (int) get('page', 1));
        $users = $this->userModel->getAll($page, ADMIN_PER_PAGE);
        $total = $this->userModel->countAll();
        $totalPages = (int) ceil($total / ADMIN_PER_PAGE);

        include dirname(__DIR__, 2) . '/views/admin/users/index.php';
    }

    // ---- POST /admin/users/{id}/delete ----
    public function deleteUser(int $id): void {
        verifyCsrf();

        // Jangan hapus diri sendiri
        if ($id === currentUserId()) {
            flash('error', 'Kamu tidak bisa menghapus akun dirimu sendiri.');
            redirect('/admin/users');
        }

        if ($this->userModel->delete($id)) {
            flash('success', 'User berhasil dihapus.');
        } else {
            flash('error', 'Gagal menghapus user.');
        }
        redirect('/admin/users');
    }

    // ---- POST /admin/users/{id}/role ----
    public function updateRole(int $id): void {
        verifyCsrf();

        $role = post('role');
        if (!in_array($role, ['admin', 'user'])) {
            flash('error', 'Role tidak valid.');
            redirect('/admin/users');
        }

        if ($id === currentUserId() && $role !== 'admin') {
            flash('error', 'Kamu tidak bisa menghapus role admin dirimu sendiri.');
            redirect('/admin/users');
        }

        if ($this->userModel->updateRole($id, $role)) {
            flash('success', 'Role user berhasil diperbarui.');
        } else {
            flash('error', 'Gagal memperbarui role.');
        }
        redirect('/admin/users');
    }

    // ====================================================
    // Private Helper
    // ====================================================

    private function validateArticleInput(): array|false {
        $title          = trim(post('title'));
        $author_name    = trim(post('author_name'));
        $article_source = trim(post('article_source'));
        $content        = post('content'); // Rich text HTML dari RTE

        // Strip HTML tag untuk cek apakah konten benar-benar ada isinya
        $contentPlain = trim(strip_tags($content));

        $errors = [];
        if (empty($title))         $errors[] = 'Judul artikel wajib diisi.';
        if (strlen($title) > 200)  $errors[] = 'Judul maksimal 200 karakter.';
        if (empty($author_name))   $errors[] = 'Nama penulis wajib diisi.';
        if (empty($contentPlain))  $errors[] = 'Konten artikel wajib diisi.';

        if ($errors) {
            flash('error', implode('<br>', $errors));
            return false;
        }

        return [
            'title'          => $title,
            'author_name'    => $author_name,
            'article_source' => $article_source ?: null,
            'content'        => $content,
        ];
    }
}
