# Flutter + Supabase Debugging Task

## Project Overview

Saya memiliki project mobile app Flutter dengan backend Supabase untuk aplikasi resep makanan.

Tolong lakukan debugging dan perbaikan secara terstruktur tanpa mengubah UI/design yang sudah ada.

Fokus utama adalah fixing bug, stabilitas aplikasi, dan memastikan semua fitur berjalan normal.

---

# BUG - Data Resep, Detail Page, dan Bookmark Bermasalah

## Kondisi Saat Ini

### Masalah Data Resep

Pada tabel Supabase `recipes`, terdapat total 4 data resep.

Namun:
- halaman Home hanya menampilkan 2 resep
- halaman Search tidak menampilkan satupun resep
- padahal seharusnya seluruh resep tampil

---

### Masalah Detail Recipe

Ketika user menekan card resep:
- aplikasi seharusnya membuka halaman detail resep
- tetapi malah membuka halaman putih kosong yang hanya memiliki tombol "Start Cooking Mode"

Sedangkan halaman cooking mode hijau seharusnya hanya muncul setelah tombol tersebut ditekan.

Flow yang benar:
1. Klik card resep
2. Masuk ke halaman detail resep
3. Tekan tombol "Start Cooking Mode"
4. Baru masuk ke halaman cooking mode hijau

---

### Masalah Bookmark

Icon bookmark pada card resep tidak bisa digunakan untuk menyimpan resep.

Expected behavior:
- Klik bookmark → resep tersimpan
- Klik lagi → bookmark terhapus
- Icon berubah sesuai status tersimpan
- Halaman Saved menampilkan bookmark user

---

# Kemungkinan Penyebab

Tolong cek kemungkinan masalah berikut:

## Query & Data Fetching

- Query Supabase menggunakan `.limit()`
- Ada filter difficulty/category yang tidak disengaja
- Kesalahan pada `.from('recipes').select()`
- Error pagination
- Search query salah
- Search keyword kosong tidak ditangani

## State Management

- State management tidak refresh
- FutureBuilder / StreamBuilder bermasalah
- State bookmark tidak update
- Data async tidak dipanggil ulang

## Parsing & Model

- Parsing model Recipe gagal
- Ada field null yang menyebabkan item tidak dirender
- Null safety issue
- Mapping JSON bermasalah

## Routing & Navigation

- Named routes salah
- MaterialPageRoute salah
- Halaman detail tertimpa cooking mode
- Argumen recipe tidak terkirim
- Conditional rendering salah
- Widget tree bermasalah

## Bookmark & Supabase

- Function onTap/onPressed tidak terpanggil
- Insert bookmark gagal
- Delete bookmark gagal
- Supabase auth tidak terbaca
- User session null
- Relasi bookmarks dengan recipes salah
- RLS policy Supabase bermasalah
- Query save/delete bookmark salah
- Exception tertangkap silent try-catch

---

# Expected Result

## Home Page

- Semua resep tampil dengan benar
- Tidak ada data yang hilang

## Search Page

- Semua resep tampil ketika keyword kosong
- Search berjalan normal ketika user mengetik keyword

## Detail Recipe Page

Halaman detail harus menampilkan:
- title
- description
- ingredients
- steps
- difficulty
- image

Dan:
- tidak langsung masuk cooking mode
- cooking mode hanya muncul setelah tombol ditekan

## Bookmark

- Bookmark dapat disimpan
- Bookmark dapat dihapus
- Icon bookmark berubah sesuai status
- Halaman Saved menampilkan data bookmark user

---

# Debugging Requirement

Tambahkan logging/debugging yang jelas.

Contoh:

```dart
debugPrint('Total recipes fetched: ${recipes.length}');
debugPrint('Recipe clicked: ${recipe.id}');
debugPrint('Bookmark insert failed: $e');
debugPrint('Current user: ${supabase.auth.currentUser?.id}');