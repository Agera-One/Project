<?php
// app/models/User.php
// ============================================================
// Model User - Semua interaksi dengan tabel `users`
// ============================================================

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Cari user berdasarkan username
     */
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare(
            'SELECT id, username, password, role, created_at FROM users WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Cari user berdasarkan ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT id, username, role, created_at FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * ============================================================
     * LOGIKA PASSWORD: MD5 Legacy + Bcrypt Modern
     * ============================================================
     *
     * Strategi "Upgrade on Login":
     * 1. Pertama coba verifikasi dengan bcrypt (password_verify)
     * 2. Jika gagal, coba dengan MD5 (legacy)
     * 3. Jika MD5 cocok → login berhasil + upgrade hash ke bcrypt secara otomatis
     * 4. User baru SELALU menggunakan bcrypt
     *
     * ⚠️  MD5 tidak aman untuk password! Mekanisme ini hanya untuk
     *     kompatibilitas mundur. Upgrade dilakukan transparan ke user.
     */
    public function verifyPassword(string $inputPassword, array $user): bool {
        $stored = $user['password'];

        // 1. Coba bcrypt terlebih dahulu (password baru / sudah di-upgrade)
        if (password_verify($inputPassword, $stored)) {
            return true;
        }

        // 2. Fallback: cek MD5 (password lama)
        if ($stored === md5($inputPassword)) {
            // ✅ MD5 cocok → upgrade ke bcrypt secara otomatis (transparan)
            $this->upgradePasswordToBcrypt($user['id'], $inputPassword);
            return true;
        }

        return false;
    }

    /**
     * Upgrade password dari MD5 ke bcrypt
     * Dipanggil otomatis saat login dengan password MD5 berhasil
     */
    private function upgradePasswordToBcrypt(int $userId, string $plainPassword): void {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $userId]);
    }

    /**
     * Registrasi user baru (SELALU menggunakan bcrypt)
     */
    public function register(string $username, string $password, string $role = 'user'): bool {
        // Cek username sudah ada
        if ($this->findByUsername($username)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, role) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$username, $hash, $role]);
    }

    /**
     * Ambil semua user (untuk admin panel)
     */
    public function getAll(int $page = 1, int $perPage = ADMIN_PER_PAGE): array {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare(
            'SELECT id, username, role, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total user
     */
    public function countAll(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    /**
     * Hapus user
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Update role user
     */
    public function updateRole(int $id, string $role): bool {
        $stmt = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        return $stmt->execute([$role, $id]);
    }
}
