// lib/features/bookmark/presentation/providers/bookmark_provider.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:resepku/core/constants/app_constants.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

// ── Bookmarked Recipes (untuk halaman Saved) ──────────────────────────────────
final bookmarkedRecipesProvider = FutureProvider<List<RecipeModel>>((ref) async {
  // BUG FIX: Ambil user langsung dari Supabase client, bukan dari provider cache
  final supabase = Supabase.instance.client;
  final user = supabase.auth.currentUser;
  debugPrint('[bookmarkedRecipesProvider] user=${user?.id}');
  if (user == null) return [];

  try {
    final response = await supabase
        .from(AppConstants.bookmarksTable)
        .select('recipe_id, recipes(*, categories(name))')
        .eq('user_id', user.id)
        .order('created_at', ascending: false);

    final list = (response as List)
        .where((e) => e['recipes'] != null)
        .map((e) {
          try {
            return RecipeModel.fromMap(e['recipes'] as Map<String, dynamic>);
          } catch (err) {
            debugPrint('[bookmarkedRecipesProvider] Parse error: $err');
            return null;
          }
        })
        .whereType<RecipeModel>()
        .toList();

    debugPrint('[bookmarkedRecipesProvider] Fetched ${list.length} bookmarks');
    return list;
  } catch (e) {
    debugPrint('[bookmarkedRecipesProvider] ERROR: $e');
    return [];
  }
});

// ── Bookmark Notifier ─────────────────────────────────────────────────────────
// BUG FIX: Tidak lagi menggunakan ref.watch di dalam build()
// yang menyebabkan rebuild loop tak terbatas.
// State diinisialisasi langsung dari Supabase.
class BookmarkNotifier extends AsyncNotifier<Set<String>> {
  @override
  Future<Set<String>> build() async {
    // BUG FIX: Ambil user dari Supabase client langsung, bukan provider
    final supabase = Supabase.instance.client;
    final user = supabase.auth.currentUser;
    debugPrint('[BookmarkNotifier.build] user=${user?.id}');
    if (user == null) return {};

    try {
      final response = await supabase
          .from(AppConstants.bookmarksTable)
          .select('recipe_id')
          .eq('user_id', user.id);

      final ids = (response as List)
          .map((e) => e['recipe_id'] as String)
          .toSet();

      debugPrint('[BookmarkNotifier.build] Loaded ${ids.length} bookmarks');
      return ids;
    } catch (e) {
      debugPrint('[BookmarkNotifier.build] ERROR: $e');
      return {};
    }
  }

  Future<void> toggle(String recipeId) async {
    // BUG FIX: Gunakan Supabase.instance.client langsung
    final supabase = Supabase.instance.client;
    final user = supabase.auth.currentUser;

    debugPrint('[BookmarkNotifier.toggle] user=${user?.id} recipe=$recipeId');

    if (user == null) {
      debugPrint('[BookmarkNotifier.toggle] ERROR: user is null, cannot toggle bookmark');
      return;
    }

    final currentIds = state.value ?? {};

    try {
      if (currentIds.contains(recipeId)) {
        // Hapus bookmark
        await supabase
            .from(AppConstants.bookmarksTable)
            .delete()
            .eq('user_id', user.id)
            .eq('recipe_id', recipeId);

        // Update state lokal langsung (optimistic update)
        state = AsyncData(Set.from(currentIds)..remove(recipeId));
        debugPrint('[BookmarkNotifier.toggle] Removed bookmark for $recipeId');
      } else {
        // Tambah bookmark
        await supabase
            .from(AppConstants.bookmarksTable)
            .insert({'user_id': user.id, 'recipe_id': recipeId});

        // Update state lokal langsung
        state = AsyncData(Set.from(currentIds)..add(recipeId));
        debugPrint('[BookmarkNotifier.toggle] Added bookmark for $recipeId');
      }

      // Refresh halaman Saved
      ref.invalidate(bookmarkedRecipesProvider);
    } catch (e) {
      debugPrint('[BookmarkNotifier.toggle] ERROR: $e');
      // Jangan update state jika gagal
      rethrow;
    }
  }

  bool isBookmarked(String recipeId) {
    return state.value?.contains(recipeId) ?? false;
  }
}

final bookmarkNotifierProvider =
    AsyncNotifierProvider<BookmarkNotifier, Set<String>>(BookmarkNotifier.new);
