# Cara Running

cd C:\xampp\htdocs\artikel-app
php -S localhost:8000 -t public

---

## 📁 Struktur Folder

```
artikel-app/
├── .htaccess                      ← Redirect root ke /public
├── config/
│   ├── app.php                    ← Konstanta & autoloader global
│   └── database.php               ← Koneksi PDO (Singleton)
│
├── app/
│   ├── controllers/
│   │   ├── AuthController.php     ← Login, Register, Logout
│   │   ├── ArticleController.php  ← Public: list & detail artikel
│   │   └── AdminController.php    ← Admin: CRUD artikel & user
│   │
│   ├── models/
│   │   ├── User.php               ← Model user + logika password MD5→bcrypt
│   │   └── Article.php            ← Model artikel + pagination + search
│   │
│   ├── middleware/
│   │   └── AuthMiddleware.php     ← Proteksi route (requireLogin, requireAdmin)
│   │
│   └── helpers/
│       ├── functions.php          ← Utility: redirect, flash, csrf, format, dll
│       └── auth_helper.php        ← Session login/logout + JWT encode/decode
│
├── views/
│   ├── layouts/
│   │   ├── header.php             ← Header & navbar publik
│   │   └── footer.php             ← Footer publik
│   │
│   ├── home.php                   ← Landing page
│   │
│   ├── auth/
│   │   ├── login.php              ← Halaman login
│   │   └── register.php           ← Halaman register
│   │
│   ├── articles/
│   │   ├── index.php              ← Daftar artikel + search + pagination
│   │   └── show.php               ← Detail artikel (full content)
│   │
│   ├── admin/
│   │   ├── layout/
│   │   │   ├── header.php         ← Sidebar + topbar admin
│   │   │   └── footer.php         ← Closing tags admin
│   │   ├── dashboard.php          ← Dashboard dengan stat cards & chart
│   │   ├── articles/
│   │   │   ├── index.php          ← Tabel artikel admin + CRUD actions
│   │   │   └── form.php           ← Form create/edit + Rich Text Editor
│   │   └── users/
│   │       └── index.php          ← Daftar user + ubah role + hapus
│   │
│   └── errors/
│       ├── 404.php
│       └── 403.php
│
├── public/                        ← 🌐 Document Root Apache/Nginx
│   ├── index.php                  ← Front Controller (router utama)
│   ├── .htaccess                  ← URL rewrite ke index.php
│   └── assets/
│       ├── css/
│       │   ├── main.css           ← Stylesheet publik (editorial design)
│       │   └── admin.css          ← Stylesheet admin dashboard
│       └── js/
│           ├── main.js            ← JavaScript publik
│           └── admin.js           ← JavaScript admin
│
├── uploads/                       ← Folder upload (future use)
└── db_artikel_updated.sql         ← SQL tambahan (opsional)
```

---

## 🚀 Panduan Setup Langkah demi Langkah

### 1. Persyaratan Server
- **PHP** ≥ 8.0
- **MySQL** ≥ 5.7 atau **MariaDB** ≥ 10.4
- **Apache** dengan `mod_rewrite` aktif
- **PHP Extensions:** `pdo`, `pdo_mysql`, `mbstring`, `openssl`

### 2. Install & Konfigurasi

```bash
# 1. Letakkan folder di dalam htdocs / www
cp -r artikel-app/ /var/www/html/
# atau di XAMPP:
cp -r artikel-app/ C:/xampp/htdocs/

# 2. Pastikan mod_rewrite aktif (Apache)
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 3. Setup Database

```bash
# Buat database baru
mysql -u root -p
CREATE DATABASE db_artikel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
exit;

# Import data
mysql -u root -p db_artikel < db_artikel.sql
```

### 4. Konfigurasi Koneksi

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_artikel');
define('DB_USER', 'root');      // ← Ganti dengan username MySQL kamu
define('DB_PASS', '');          // ← Ganti dengan password MySQL kamu
```

Edit file `config/app.php`:

```php
// URL sesuai path instalasi kamu
define('APP_URL', 'http://localhost/artikel-app/public');

// Ganti dengan string random panjang untuk keamanan JWT
define('JWT_SECRET', 'ISI_DENGAN_STRING_RANDOM_MINIMAL_32_KARAKTER_DI_SINI');
```

### 5. Konfigurasi Apache Virtual Host (Opsional)

```apache
<VirtualHost *:80>
    ServerName artikel.local
    DocumentRoot /var/www/html/artikel-app/public

    <Directory /var/www/html/artikel-app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Jika menggunakan virtual host, ubah `APP_URL` menjadi:
```php
define('APP_URL', 'http://artikel.local');
```

---

## 🔐 Akun Default (dari db_artikel.sql)

| Username | Password | Role   | Status Hash |
|----------|----------|--------|-------------|
| admin    | admin123 | admin  | MD5 (akan auto-upgrade ke bcrypt saat login) |
| user     | user123  | user   | MD5 (akan auto-upgrade ke bcrypt saat login) |

> ⚠️ **Catatan:** Password di atas adalah contoh. Pastikan kamu decode MD5 dari database untuk mengetahui password aslinya. Atau reset via SQL:
> ```sql
> -- Reset password admin menjadi "admin123" (bcrypt)
> UPDATE users SET password = '$2y$12$xxxHASHxxx' WHERE username = 'admin';
> -- Generate hash bcrypt via PHP: echo password_hash('admin123', PASSWORD_BCRYPT);
> ```

---

## 🔑 Sistem Autentikasi

### Strategi Password: MD5 Legacy + Bcrypt Modern

Karena password lama menggunakan MD5, sistem menggunakan strategi **"Upgrade on Login"**:

```
User login
    │
    ├─ Coba verifikasi dengan bcrypt (password_verify)
    │       └─ ✅ Cocok → Login berhasil
    │
    └─ Fallback: cek MD5 (md5($input) === $stored)
            ├─ ✅ Cocok → Login berhasil
            │         + Otomatis upgrade hash ke bcrypt (transparan!)
            └─ ❌ Tidak cocok → Login gagal
```

**Keunggulan:** User lama bisa login normal, dan password mereka otomatis di-upgrade ke bcrypt tanpa perlu reset password. User baru selalu menggunakan bcrypt.

### Session Management

Sistem menggunakan PHP Native Session dengan proteksi:
- `session_regenerate_id(true)` saat login (mencegah session fixation)
- `cookie_httponly = 1` (mencegah akses JavaScript ke cookie)
- `cookie_samesite = Strict` (mencegah CSRF via cookie)
- CSRF token pada semua form POST

---

## 🛡 RBAC (Role-Based Access Control)

### Route Map

```
PUBLIC (Siapa saja):
  GET  /                          → Landing page
  GET  /articles                  → Daftar artikel
  GET  /articles/{id}             → Detail artikel
  GET  /auth/login                → Form login
  POST /auth/login                → Proses login
  GET  /auth/register             → Form register
  POST /auth/register             → Proses register
  GET  /auth/logout               → Logout

PROTECTED - Admin Only:
  GET  /admin/dashboard           → Dashboard admin
  GET  /admin/articles            → Tabel artikel
  GET  /admin/articles/create     → Form buat artikel
  POST /admin/articles/store      → Simpan artikel baru
  GET  /admin/articles/{id}/edit  → Form edit artikel
  POST /admin/articles/{id}/update→ Update artikel
  POST /admin/articles/{id}/delete→ Hapus artikel
  GET  /admin/users               → Daftar user
  POST /admin/users/{id}/delete   → Hapus user
  POST /admin/users/{id}/role     → Ubah role user
```

---

## 🎨 Desain UI/UX

### Filosofi Desain: **Editorial Magazine**
- **Tipografi:** DM Serif Display (headings) + DM Sans (body)
- **Palette:** Deep Slate `#1a1614` + Warm Cream `#faf7f2` + Terracotta `#c85a2e`
- **Spacing:** Sistem 8px dengan generous white space
- **Motion:** Subtle fade-up animations, hover effects halus

### Komponen Utama

| Komponen | Deskripsi |
|----------|-----------|
| Landing Page | Hero section + Featured artikel + Grid 6 artikel + CTA |
| Artikel List | Search bar + Grid responsif + Pagination |
| Artikel Detail | Typography-first + Breadcrumb + Related articles |
| Login/Register | Two-column layout (visual + form) |
| Admin Dashboard | Sidebar nav + Stat cards + Bar chart + Recent table |
| Admin Form | Rich Text Editor (vanilla JS contenteditable) |
| Admin Users | Tabel user + Role management inline |

---

## ⚡ Fitur Rich Text Editor

Editor artikel menggunakan `contenteditable` (tanpa library eksternal):
- **Format:** Bold, Italic, Underline, Strikethrough
- **Heading:** H2, H3, H4
- **List:** Bullet list, Numbered list, Blockquote
- **Alignment:** Left, Center, Right
- **Link insert:** Via prompt dialog
- **Preview mode:** Toggle live preview
- **Word count & reading time:** Real-time
- **Paste as plain text:** Otomatis strip formatting eksternal

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|-------------|
| SQL Injection | PDO Prepared Statements |
| XSS | `htmlspecialchars()` via fungsi `e()` |
| CSRF | Token di setiap form POST |
| Session Fixation | `session_regenerate_id(true)` saat login |
| Password Security | bcrypt dengan cost 12 (upgrade otomatis dari MD5) |
| Clickjacking | Header `X-Frame-Options: DENY` |
| Directory Listing | `Options -Indexes` di .htaccess |
| Path Traversal | Block akses langsung ke /app, /config, /views |

---

## 📝 Catatan Pengembangan Lanjutan

1. **Email verification** — Tambahkan kolom `email` dan `email_verified_at` ke tabel `users`
2. **Image upload** — Implementasi upload gambar thumbnail untuk artikel
3. **Categories/Tags** — Tambahkan tabel `categories` dan `article_categories`
4. **Comment system** — Tabel `comments` dengan moderasi admin
5. **API endpoint** — JWT sudah tersedia di `auth_helper.php`, tinggal tambahkan endpoint JSON
6. **Cache** — Implementasi file-based caching untuk artikel populer
7. **Rate limiting** — Tambahkan rate limit pada form login untuk mencegah brute force

---

*Dibuat dengan PHP Native tanpa framework. © <?= date('Y') ?> ArtikelHub.*
