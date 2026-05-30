<?php
// app/helpers/auth_helper.php
// ============================================================
// Manajemen Authentication: Session + JWT Hybrid
// ============================================================

/**
 * Simpan user ke session setelah login berhasil
 */
function loginUser(array $user): void {
    // Regenerate session ID untuk mencegah session fixation
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
        'login_at' => time(),
    ];
}

/**
 * Hapus session (logout)
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']['id']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Ambil data user yang sedang login
 */
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Ambil ID user yang sedang login
 */
function currentUserId(): ?int {
    return $_SESSION['user']['id'] ?? null;
}

// ============================================================
// JWT Utility (untuk API endpoint jika dibutuhkan)
// ============================================================

/**
 * Encode JWT token sederhana (tanpa library eksternal)
 * Algoritma: HS256
 */
function jwtEncode(array $payload): string {
    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode($payload));
    $sig     = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}

/**
 * Decode dan validasi JWT token
 * Returns payload array atau null jika tidak valid
 */
function jwtDecode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $sig] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode(base64url_decode($payload), true);

    // Cek expiry
    if (isset($data['exp']) && $data['exp'] < time()) return null;

    return $data;
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

/**
 * Buat JWT token untuk user (berlaku 2 jam)
 */
function createJWT(array $user): string {
    return jwtEncode([
        'sub'      => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
        'iat'      => time(),
        'exp'      => time() + SESSION_LIFETIME,
    ]);
}
