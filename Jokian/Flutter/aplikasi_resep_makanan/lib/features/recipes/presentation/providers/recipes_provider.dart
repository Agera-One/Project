// lib/features/recipes/presentation/providers/recipes_provider.dart

import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:resepku/core/constants/app_constants.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';
import 'package:resepku/features/recipes/data/models/category_model.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:flutter/material.dart';

// ── Categories ────────────────────────────────────────────────────────────────
final categoriesProvider = FutureProvider<List<CategoryModel>>((ref) async {
  final supabase = ref.watch(supabaseProvider);
  final response = await supabase
      .from(AppConstants.categoriesTable)
      .select()
      .order('name');
  debugPrint('[Categories] Fetched: ${(response as List).length}');
  return (response as List).map((e) => CategoryModel.fromMap(e)).toList();
});

// ── All Approved Recipes ──────────────────────────────────────────────────────
// BUG FIX: Tidak ada .limit() yang memotong jumlah resep
final recipesProvider = FutureProvider.family<List<RecipeModel>, String?>(
  (ref, categoryId) async {
    final supabase = ref.watch(supabaseProvider);

    var query = supabase
        .from(AppConstants.recipesTable)
        .select('*, categories(name), profiles(full_name)')
        .eq('is_approved', true);

    if (categoryId != null && categoryId.isNotEmpty) {
      query = query.eq('category_id', categoryId);
    }

    final response = await query.order('created_at', ascending: false);
    final list = (response as List).map((e) {
      try {
        return RecipeModel.fromMap(e);
      } catch (err) {
        debugPrint('[recipesProvider] Parse error for item: $err\nData: $e');
        return null;
      }
    }).whereType<RecipeModel>().toList();

    debugPrint('[recipesProvider] categoryId=$categoryId → fetched: ${list.length}');
    return list;
  },
);

// ── Featured Recipes ──────────────────────────────────────────────────────────
final featuredRecipesProvider = FutureProvider<List<RecipeModel>>((ref) async {
  final supabase = ref.watch(supabaseProvider);
  final response = await supabase
      .from(AppConstants.recipesTable)
      .select('*, categories(name), profiles(full_name)')
      .eq('is_approved', true)
      .eq('is_featured', true)
      .order('created_at', ascending: false)
      .limit(5);
  debugPrint('[featuredRecipesProvider] fetched: ${(response as List).length}');
  return (response as List).map((e) => RecipeModel.fromMap(e)).toList();
});

// ── Single Recipe ─────────────────────────────────────────────────────────────
final recipeDetailProvider = FutureProvider.family<RecipeModel?, String>(
  (ref, recipeId) async {
    final supabase = ref.watch(supabaseProvider);
    debugPrint('[recipeDetailProvider] Fetching id=$recipeId');
    try {
      final response = await supabase
          .from(AppConstants.recipesTable)
          .select('*, categories(name), profiles(full_name)')
          .eq('id', recipeId)
          .single();
      final recipe = RecipeModel.fromMap(response);
      debugPrint('[recipeDetailProvider] OK: ${recipe.title}, steps=${recipe.steps.length}, ingredients=${recipe.ingredients.length}');
      return recipe;
    } catch (e) {
      debugPrint('[recipeDetailProvider] ERROR: $e');
      return null;
    }
  },
);

// ── Search ─────────────────────────────────────────────────────────────────────
final searchQueryProvider = StateProvider<String>((ref) => '');
final searchDifficultyProvider = StateProvider<String?>((ref) => null);

// BUG FIX: Saat query kosong DAN tidak ada filter → tampilkan SEMUA resep
// bukan return [] seperti sebelumnya
final searchResultsProvider = FutureProvider<List<RecipeModel>>((ref) async {
  final query = ref.watch(searchQueryProvider);
  final difficulty = ref.watch(searchDifficultyProvider);

  final supabase = ref.watch(supabaseProvider);
  var dbQuery = supabase
      .from(AppConstants.recipesTable)
      .select('*, categories(name), profiles(full_name)')
      .eq('is_approved', true);

  if (query.isNotEmpty) {
    dbQuery = dbQuery.ilike('title', '%$query%');
  }
  if (difficulty != null && difficulty.isNotEmpty) {
    dbQuery = dbQuery.eq('difficulty', difficulty);
  }

  final response = await dbQuery
      .order('created_at', ascending: false)
      .limit(50);

  final list = (response as List).map((e) {
    try {
      return RecipeModel.fromMap(e);
    } catch (err) {
      debugPrint('[searchResultsProvider] Parse error: $err');
      return null;
    }
  }).whereType<RecipeModel>().toList();

  debugPrint('[searchResultsProvider] query="$query" difficulty=$difficulty → ${list.length} results');
  return list;
});

// ── Recipe Notifier (CRUD) ────────────────────────────────────────────────────
class RecipeNotifier extends AsyncNotifier<void> {
  @override
  Future<void> build() async {}

  Future<String> createRecipe({
    required String title,
    required String description,
    required List<String> ingredients,
    required List<Map<String, String>> steps,
    required String difficulty,
    required String categoryId,
    Uint8List? imageBytes,
    String? imageFileName,
  }) async {
    state = const AsyncLoading();
    try {
      final supabase = ref.read(supabaseProvider);
      final user = supabase.auth.currentUser;
      debugPrint('[createRecipe] user=${user?.id}');
      if (user == null) throw Exception('Sesi habis, silakan login ulang');

      String? imageUrl;
      if (imageBytes != null && imageFileName != null) {
        final fileName =
            '${user.id}_${DateTime.now().millisecondsSinceEpoch}_$imageFileName';
        await supabase.storage
            .from(AppConstants.recipeBucket)
            .uploadBinary(fileName, imageBytes);
        imageUrl = supabase.storage
            .from(AppConstants.recipeBucket)
            .getPublicUrl(fileName);
        debugPrint('[createRecipe] Image uploaded: $imageUrl');
      }

      final response = await supabase
          .from(AppConstants.recipesTable)
          .insert({
            'title': title,
            'description': description,
            'ingredients': ingredients,
            'steps': steps,
            'difficulty': difficulty,
            'category_id': categoryId,
            'image_url': imageUrl,
            'created_by': user.id,
            'is_approved': false,
            'is_featured': false,
          })
          .select()
          .single();

      debugPrint('[createRecipe] Created id=${response['id']}');
      state = const AsyncData(null);
      // Invalidate semua provider yang relevan
      ref.invalidate(recipesProvider);
      ref.invalidate(pendingRecipesProvider);
      ref.invalidate(adminStatsProvider);
      return response['id'] as String;
    } catch (e, st) {
      debugPrint('[createRecipe] ERROR: $e');
      state = AsyncError(e, st);
      rethrow;
    }
  }

  Future<void> updateRecipe({
    required String recipeId,
    Map<String, dynamic>? updates,
  }) async {
    final supabase = ref.read(supabaseProvider);
    await supabase
        .from(AppConstants.recipesTable)
        .update(updates ?? {})
        .eq('id', recipeId);
    ref.invalidate(recipesProvider);
    ref.invalidate(recipeDetailProvider(recipeId));
    ref.invalidate(adminStatsProvider);
  }

  Future<void> deleteRecipe(String recipeId) async {
    final supabase = ref.read(supabaseProvider);
    await supabase
        .from(AppConstants.recipesTable)
        .delete()
        .eq('id', recipeId);
    ref.invalidate(recipesProvider);
    ref.invalidate(pendingRecipesProvider);
    ref.invalidate(adminStatsProvider);
  }

  Future<void> toggleApprove(String recipeId, bool approve) async {
    debugPrint('[toggleApprove] id=$recipeId approve=$approve');
    await updateRecipe(
      recipeId: recipeId,
      updates: {'is_approved': approve},
    );
    ref.invalidate(pendingRecipesProvider);
    ref.invalidate(recipesProvider);
    ref.invalidate(adminStatsProvider);
  }

  Future<void> toggleFeatured(String recipeId, bool featured) async {
    await updateRecipe(
      recipeId: recipeId,
      updates: {'is_featured': featured},
    );
    ref.invalidate(featuredRecipesProvider);
  }
}

final recipeNotifierProvider =
    AsyncNotifierProvider<RecipeNotifier, void>(RecipeNotifier.new);

// ── Pending Recipes (Admin) ───────────────────────────────────────────────────
final pendingRecipesProvider = FutureProvider<List<RecipeModel>>((ref) async {
  final supabase = ref.watch(supabaseProvider);
  debugPrint('[pendingRecipesProvider] Fetching pending recipes...');
  final response = await supabase
      .from(AppConstants.recipesTable)
      .select('*, categories(name), profiles(full_name)')
      .eq('is_approved', false)
      .order('created_at', ascending: false);

  final list = (response as List).map((e) {
    try {
      return RecipeModel.fromMap(e);
    } catch (err) {
      debugPrint('[pendingRecipesProvider] Parse error: $err');
      return null;
    }
  }).whereType<RecipeModel>().toList();

  debugPrint('[pendingRecipesProvider] Found: ${list.length}');
  return list;
});

// ── Admin Stats ────────────────────────────────────────────────────────────────
class AdminStats {
  final int totalRecipes;
  final int pendingCount;
  final int activeUsers;
  final int featuredCount;

  const AdminStats({
    required this.totalRecipes,
    required this.pendingCount,
    required this.activeUsers,
    required this.featuredCount,
  });
}

final adminStatsProvider = FutureProvider<AdminStats>((ref) async {
  final supabase = ref.watch(supabaseProvider);

  final totalRes = await supabase
      .from(AppConstants.recipesTable)
      .select('id')
      .count(CountOption.exact);

  final pendingRes = await supabase
      .from(AppConstants.recipesTable)
      .select('id')
      .eq('is_approved', false)
      .count(CountOption.exact);

  final usersRes = await supabase
      .from(AppConstants.profilesTable)
      .select('id')
      .count(CountOption.exact);

  final featuredRes = await supabase
      .from(AppConstants.recipesTable)
      .select('id')
      .eq('is_featured', true)
      .count(CountOption.exact);

  debugPrint('[adminStats] total=${totalRes.count} pending=${pendingRes.count} users=${usersRes.count}');
  return AdminStats(
    totalRecipes: totalRes.count,
    pendingCount: pendingRes.count,
    activeUsers: usersRes.count,
    featuredCount: featuredRes.count,
  );
});
