<?php
// app/models/Article.php
// ============================================================
// Model Article - Semua interaksi dengan tabel `articles`
// ============================================================

class Article {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Ambil semua artikel dengan pagination (untuk publik & admin)
     */
    public function getAll(int $page = 1, int $perPage = ARTICLES_PER_PAGE, string $search = ''): array {
        $offset = ($page - 1) * $perPage;

        $sql = '
            SELECT a.id, a.title, a.author_name, a.article_source,
                   a.content, a.author_id, a.created_at,
                   u.username as author_username
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
        ';

        if ($search) {
            $sql .= ' WHERE a.title LIKE ? OR a.author_name LIKE ? OR a.content LIKE ?';
            $sql .= ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';
            $stmt = $this->db->prepare($sql);
            $like = "%$search%";
            $stmt->execute([$like, $like, $like, $perPage, $offset]);
        } else {
            $sql .= ' ORDER BY a.created_at DESC LIMIT ? OFFSET ?';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$perPage, $offset]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Hitung total artikel (dengan atau tanpa filter search)
     */
    public function countAll(string $search = ''): int {
        if ($search) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM articles WHERE title LIKE ? OR author_name LIKE ? OR content LIKE ?'
            );
            $like = "%$search%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $this->db->query('SELECT COUNT(*) FROM articles');
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Ambil artikel terbaru (untuk landing page hero section)
     */
    public function getLatest(int $limit = 3): array {
        $stmt = $this->db->prepare('
            SELECT a.id, a.title, a.author_name, a.content, a.created_at,
                   u.username as author_username
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu artikel berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('
            SELECT a.*, u.username as author_username
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.id = ?
            LIMIT 1
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Buat artikel baru
     */
    public function create(array $data): int|false {
        $stmt = $this->db->prepare('
            INSERT INTO articles (title, author_name, article_source, content, author_id)
            VALUES (?, ?, ?, ?, ?)
        ');
        $success = $stmt->execute([
            $data['title'],
            $data['author_name'],
            $data['article_source'] ?? null,
            $data['content'],
            $data['author_id'],
        ]);
        return $success ? (int) $this->db->lastInsertId() : false;
    }

    /**
     * Update artikel
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare('
            UPDATE articles
            SET title = ?, author_name = ?, article_source = ?, content = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['title'],
            $data['author_name'],
            $data['article_source'] ?? null,
            $data['content'],
            $id,
        ]);
    }

    /**
     * Hapus artikel
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM articles WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Ambil artikel terkait (berdasarkan kesamaan penulis, exclude current)
     */
    public function getRelated(int $articleId, int $authorId, int $limit = 3): array {
        $stmt = $this->db->prepare('
            SELECT id, title, author_name, content, created_at
            FROM articles
            WHERE id != ? AND author_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$articleId, $authorId, $limit]);
        $related = $stmt->fetchAll();

        // Jika kurang dari limit, tambahkan artikel lain
        if (count($related) < $limit) {
            $remaining = $limit - count($related);
            $ids = array_column($related, 'id');
            $ids[] = $articleId;
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt2 = $this->db->prepare("
                SELECT id, title, author_name, content, created_at
                FROM articles
                WHERE id NOT IN ($placeholders)
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $params = array_merge($ids, [$remaining]);
            $stmt2->execute($params);
            $related = array_merge($related, $stmt2->fetchAll());
        }

        return $related;
    }

    /**
     * Statistik artikel per bulan (untuk chart admin)
     */
    public function getMonthlyStats(): array {
        $stmt = $this->db->query('
            SELECT DATE_FORMAT(created_at, "%b %Y") as month,
                   YEAR(created_at) as yr,
                   MONTH(created_at) as mo,
                   COUNT(*) as total
            FROM articles
            GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, "%b %Y")
            ORDER BY yr DESC, mo DESC
            LIMIT 6
        ');
        return array_reverse($stmt->fetchAll());
    }
}
