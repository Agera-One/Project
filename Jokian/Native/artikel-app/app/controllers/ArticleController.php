<?php
// app/controllers/ArticleController.php
// ============================================================
// Controller Artikel Publik
// ============================================================

class ArticleController {
    private Article $articleModel;

    public function __construct() {
        $this->articleModel = new Article();
    }

    // ---- GET / (Landing Page) ----
    public function index(): void {
        $latestArticles = $this->articleModel->getLatest(3);
        $featuredArticle = $latestArticles[0] ?? null;
        $gridArticles = array_slice($latestArticles, 1);

        // Semua artikel untuk grid section
        $allArticles = $this->articleModel->getAll(1, 6);
        $totalArticles = $this->articleModel->countAll();

        include dirname(__DIR__, 2) . '/views/home.php';
    }

    // ---- GET /articles (Daftar Artikel) ----
    public function list(): void {
        $page   = max(1, (int) get('page', 1));
        $search = trim(get('search', ''));

        $articles     = $this->articleModel->getAll($page, ARTICLES_PER_PAGE, $search);
        $totalArticles = $this->articleModel->countAll($search);
        $totalPages   = (int) ceil($totalArticles / ARTICLES_PER_PAGE);

        include dirname(__DIR__, 2) . '/views/articles/index.php';
    }

    // ---- GET /articles/{id} (Detail Artikel) ----
    public function show(int $id): void {
        $article = $this->articleModel->findById($id);

        if (!$article) {
            http_response_code(404);
            include dirname(__DIR__, 2) . '/views/errors/404.php';
            return;
        }

        $related = $this->articleModel->getRelated($id, $article['author_id'] ?? 0, 3);
        $readingTime = readingTime($article['content']);

        include dirname(__DIR__, 2) . '/views/articles/show.php';
    }
}
