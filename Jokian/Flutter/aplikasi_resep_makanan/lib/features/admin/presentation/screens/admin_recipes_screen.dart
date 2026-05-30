// lib/features/admin/presentation/screens/admin_recipes_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:go_router/go_router.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';

class AdminRecipesScreen extends ConsumerWidget {
  const AdminRecipesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final recipes = ref.watch(recipesProvider(null));

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Kelola Resep', style: Theme.of(context).textTheme.displaySmall),
                  const SizedBox(height: 4),
                  Text('Semua resep yang telah disetujui', style: Theme.of(context).textTheme.bodyMedium),
                ],
              ),
            ),
            Expanded(
              child: recipes.when(
                data: (list) => list.isEmpty
                    ? const Center(child: Text('Belum ada resep'))
                    : ListView.builder(
                        physics: const BouncingScrollPhysics(),
                        itemCount: list.length,
                        padding: const EdgeInsets.only(bottom: 20),
                        itemBuilder: (_, i) => _AdminRecipeRow(recipe: list[i]),
                      ),
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (e, _) => Center(child: Text('Error: $e')),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AdminRecipeRow extends ConsumerStatefulWidget {
  final RecipeModel recipe;
  const _AdminRecipeRow({required this.recipe});

  @override
  ConsumerState<_AdminRecipeRow> createState() => _AdminRecipeRowState();
}

class _AdminRecipeRowState extends ConsumerState<_AdminRecipeRow> {
  bool _isLoading = false;

  Future<void> _toggleFeatured() async {
    setState(() => _isLoading = true);
    try {
      await ref.read(recipeNotifierProvider.notifier).toggleFeatured(widget.recipe.id, !widget.recipe.isFeatured);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _delete() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Resep?'),
        content: Text('Yakin ingin menghapus "${widget.recipe.title}"? Tindakan ini tidak dapat dibatalkan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (confirm == true) {
      await ref.read(recipeNotifierProvider.notifier).deleteRecipe(widget.recipe.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final r = widget.recipe;
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 0, 20, 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8)],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: r.imageUrl != null
                ? CachedNetworkImage(imageUrl: r.imageUrl!, width: 60, height: 60, fit: BoxFit.cover)
                : Container(width: 60, height: 60, color: AppColors.surfaceVariant, child: const Icon(Icons.restaurant_menu_rounded, color: AppColors.border, size: 28)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(r.title, style: Theme.of(context).textTheme.titleMedium, maxLines: 1, overflow: TextOverflow.ellipsis),
                const SizedBox(height: 2),
                Text('${r.difficulty} · ${r.categoryName ?? ""}', style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 6),
                Row(
                  children: [
                    // Featured toggle
                    _isLoading
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                        : GestureDetector(
                            onTap: _toggleFeatured,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: r.isFeatured ? const Color(0xFFFFF9E6) : AppColors.surfaceVariant,
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: r.isFeatured ? const Color(0xFFFFC107) : AppColors.border),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(r.isFeatured ? Icons.star_rounded : Icons.star_outline_rounded,
                                      size: 13, color: r.isFeatured ? const Color(0xFFFFC107) : AppColors.textTertiary),
                                  const SizedBox(width: 3),
                                  Text(r.isFeatured ? 'Unggulan' : 'Biasa', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: r.isFeatured ? const Color(0xFFFFC107) : AppColors.textTertiary)),
                                ],
                              ),
                            ),
                          ),
                  ],
                ),
              ],
            ),
          ),
          // Delete button
          IconButton(
            onPressed: _delete,
            icon: const Icon(Icons.delete_outline_rounded, color: AppColors.error, size: 20),
            tooltip: 'Hapus resep',
          ),
        ],
      ),
    );
  }
}
