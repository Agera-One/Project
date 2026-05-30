# 🍽️ RESEPKU - Flutter Web Recipe App

> **Taste the Tradition** — Your sanctuary for culinary focus.

Aplikasi resep makanan berbasis Flutter Web yang terintegrasi penuh dengan Supabase (PostgreSQL, Auth, Storage).

---

## 🏗️ Tech Stack

| Domain | Teknologi | Versi |
|---|---|---|
| Framework UI | Flutter Web | ≥3.0 |
| State Management | Riverpod | ^2.5.1 |
| Routing | GoRouter | ^14.2.7 |
| Backend / DB | Supabase | ^2.5.6 |
| Fonts | Google Fonts (Playfair Display + DM Sans) | ^6.2.1 |

---

## 🚀 Setup & Instalasi

### 1. Prerequisites
```bash
# Pastikan Flutter sudah terinstall
flutter --version   # minimal 3.0.0

# Aktifkan web support
flutter config --enable-web
```

### 2. Clone & Install Dependencies
```bash
# Clone project
git clone <repo-url>
cd resepku

# Install dependencies
flutter pub get
```

### 3. Konfigurasi Supabase

#### a. Buat Project di Supabase
1. Buka [supabase.com](https://supabase.com) → New Project
2. Catat **Project URL** dan **Anon Key**

#### b. Setup Database (SQL Editor)
Salin dan jalankan SQL berikut di **SQL Editor** Supabase:

```sql
-- 1. EXTENSIONS & ENUMS
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE TYPE public.user_role AS ENUM ('user', 'admin');
CREATE TYPE public.recipe_difficulty AS ENUM ('Mudah', 'Medium', 'Sulit');

-- 2. TABLES
CREATE TABLE public.profiles (
  id UUID REFERENCES auth.users(id) ON DELETE CASCADE PRIMARY KEY,
  email TEXT UNIQUE NOT NULL,
  full_name TEXT,
  role public.user_role DEFAULT 'user'::public.user_role NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

CREATE TABLE public.categories (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT UNIQUE NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

CREATE TABLE public.recipes (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  title TEXT NOT NULL,
  description TEXT,
  ingredients JSONB NOT NULL,
  steps JSONB NOT NULL,
  difficulty public.recipe_difficulty NOT NULL,
  category_id UUID REFERENCES public.categories(id) ON DELETE SET NULL,
  image_url TEXT,
  created_by UUID REFERENCES public.profiles(id) ON DELETE CASCADE NOT NULL,
  is_approved BOOLEAN DEFAULT false NOT NULL,
  is_featured BOOLEAN DEFAULT false NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

CREATE TABLE public.bookmarks (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_id UUID REFERENCES public.profiles(id) ON DELETE CASCADE NOT NULL,
  recipe_id UUID REFERENCES public.recipes(id) ON DELETE CASCADE NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL,
  UNIQUE(user_id, recipe_id)
);

-- 3. INITIAL DATA
INSERT INTO public.categories (name) VALUES
  ('Makanan Tradisional'), ('Dessert'), ('Menu Diet'), ('Cepat Saji');

-- 4. TRIGGER AUTO-CREATE PROFILE
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger AS $$
BEGIN
  INSERT INTO public.profiles (id, email, full_name, role)
  VALUES (new.id, new.email, new.raw_user_meta_data->>'full_name', 'user');
  RETURN new;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE PROCEDURE public.handle_new_user();

-- 5. ROW LEVEL SECURITY
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.recipes ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.bookmarks ENABLE ROW LEVEL SECURITY;

-- Policies
CREATE POLICY "Categories viewable by everyone" ON public.categories FOR SELECT USING (true);
CREATE POLICY "Profiles viewable by everyone" ON public.profiles FOR SELECT USING (true);
CREATE POLICY "Users can update own profile" ON public.profiles FOR UPDATE USING (auth.uid() = id);
CREATE POLICY "Approved recipes viewable by everyone" ON public.recipes FOR SELECT USING (is_approved = true);
CREATE POLICY "Users can view own pending recipes" ON public.recipes FOR SELECT USING (auth.uid() = created_by);
CREATE POLICY "Authenticated users can create recipes" ON public.recipes FOR INSERT WITH CHECK (auth.uid() = created_by);
CREATE POLICY "Users can update own recipes" ON public.recipes FOR UPDATE USING (auth.uid() = created_by);
CREATE POLICY "Users can delete own recipes" ON public.recipes FOR DELETE USING (auth.uid() = created_by);
CREATE POLICY "Users can view own bookmarks" ON public.bookmarks FOR SELECT USING (auth.uid() = user_id);
CREATE POLICY "Users can create own bookmarks" ON public.bookmarks FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own bookmarks" ON public.bookmarks FOR DELETE USING (auth.uid() = user_id);
```

#### c. Setup Storage
1. Buka menu **Storage** di Supabase dashboard
2. Buat bucket baru: `recipe_images`
3. Set bucket sebagai **Public**

#### d. Konfigurasi Kredensial di App
Edit file `lib/core/constants/app_constants.dart`:
```dart
static const String supabaseUrl = 'https://YOUR_PROJECT.supabase.co';
static const String supabaseAnonKey = 'YOUR_ANON_KEY_HERE';
```

#### e. Menetapkan Admin
1. Daftar akun via aplikasi
2. Buka tabel `profiles` di Supabase Dashboard
3. Ubah kolom `role` akun Anda menjadi `admin`

---

### 4. Jalankan Aplikasi
```bash
# Chrome (recommended)
flutter run -d chrome --web-renderer canvaskit

# Dengan URL strategy (no #)
flutter run -d chrome --web-renderer canvaskit --dart-define=FLUTTER_WEB_USE_SKIA=true
```

---

## 📁 Struktur Folder (Clean Architecture)

```
lib/
├── core/
│   ├── constants/
│   │   └── app_constants.dart      # Supabase URL, table names
│   ├── router/
│   │   └── app_router.dart         # GoRouter + Auth Guards
│   ├── theme/
│   │   └── app_theme.dart          # Design system
│   └── utils/
├── features/
│   ├── auth/
│   │   ├── data/models/
│   │   │   └── profile_model.dart
│   │   └── presentation/
│   │       ├── providers/
│   │       │   └── auth_provider.dart
│   │       └── screens/
│   │           ├── login_screen.dart
│   │           └── register_screen.dart
│   ├── recipes/
│   │   ├── data/
│   │   │   ├── models/
│   │   │   │   ├── recipe_model.dart
│   │   │   │   └── category_model.dart
│   │   └── presentation/
│   │       ├── providers/
│   │       │   └── recipes_provider.dart
│   │       └── screens/
│   │           ├── home_screen.dart
│   │           ├── recipe_detail_screen.dart
│   │           ├── recipe_form_screen.dart
│   │           ├── search_screen.dart
│   │           └── saved_screen.dart
│   ├── admin/
│   │   └── presentation/screens/
│   │       ├── admin_dashboard_screen.dart
│   │       ├── admin_review_screen.dart
│   │       └── admin_recipes_screen.dart
│   └── bookmark/
│       └── presentation/providers/
│           └── bookmark_provider.dart
├── shared/
│   └── widgets/
│       ├── recipe_card.dart        # RecipeCardLarge, Small, Featured
│       └── main_scaffold.dart      # BottomNavigationBar
└── main.dart                       # Entry point + Supabase init
```

---

## ✨ Fitur Lengkap

### 👤 User
- [x] Login / Register (Email & Password)
- [x] Homepage dengan kategori & filter
- [x] Search real-time + filter kesulitan
- [x] Detail resep dengan ingredient checklist
- [x] Cooking Mode step-by-step
- [x] Bookmark / Simpan resep
- [x] Submit resep baru + upload foto

### 🔐 Admin
- [x] Dashboard analytics (stats)
- [x] Review Queue (Approve / Reject)
- [x] Kelola semua resep (toggle featured, delete)
- [x] RBAC via GoRouter redirect

---

## 🎨 Design System

| Token | Value |
|---|---|
| Primary | `#3D6B4F` (Emerald Green) |
| Accent | `#C0392B` (Crimson) |
| Background | `#FAF7F2` (Warm White) |
| Surface | `#FFFFFF` |
| Heading Font | Playfair Display |
| Body Font | DM Sans |

---

## 📦 Build untuk Production

```bash
flutter build web --release --web-renderer canvaskit
```

Output ada di folder `build/web/` — deploy ke Vercel, Netlify, atau Firebase Hosting.
