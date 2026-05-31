// lib/core/constants/app_constants.dart

class AppConstants {
  AppConstants._();

  // ── Supabase ─────────────────────────────────────────────
  // GANTI dengan credentials Supabase Anda
  static const String supabaseUrl = 'YOUR_SUPABASE_URL';
  static const String supabaseAnonKey = 'YOUR_SUPABASE_ANON_KEY';

  // ── Storage ───────────────────────────────────────────────
  static const String recipeBucket = 'recipe_images';

  // ── App ──────────────────────────────────────────────────
  static const String appName = 'Resepku';
  static const String appTagline = 'Taste the Tradition';
  static const String appVersion = '1.0.0';

  // ── Tables ────────────────────────────────────────────────
  static const String profilesTable = 'profiles';
  static const String recipesTable = 'recipes';
  static const String categoriesTable = 'categories';
  static const String bookmarksTable = 'bookmarks';

  // ── Pagination ────────────────────────────────────────────
  static const int pageSize = 10;

  // ── Difficulty Labels ─────────────────────────────────────
  static const String diffMudah = 'Mudah';
  static const String diffMedium = 'Medium';
  static const String diffSulit = 'Sulit';

  // ── Roles ─────────────────────────────────────────────────
  static const String roleUser = 'user';
  static const String roleAdmin = 'admin';
}
