// lib/features/recipes/presentation/screens/my_recipes_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';

// Provider untuk resep milik user yang sedang login
final myRecipesProvider = FutureProvider<List<RecipeModel>>((ref) async {
  final supabase = ref.watch(supabaseProvider);
  final user = supabase.auth.currentUser;
  if (user == null) return [];

  final response = await supabase
      .from('recipes')
      .select('*, categories(name)')
      .eq('created_by', user.id)
      .order('created_at', ascending: false);

  return (response as List).map((e) => RecipeModel.fromMap(e)).toList();
});

class MyRecipesScreen extends ConsumerWidget {
  const MyRecipesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final myRecipes = ref.watch(myRecipesProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        // FIX 3: Tombol kembali
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: AppColors.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text(
          'Status Resep Saya',
          style: GoogleFonts.playfairDisplay(
              fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
        ),
      ),
      body: myRecipes.when(
        data: (list) {
          if (list.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('📝', style: TextStyle(fontSize: 64)),
                  const SizedBox(height: 16),
                  Text('Belum ada resep yang dikirim',
                      style: Theme.of(context)
                          .textTheme
                          .headlineSmall
                          ?.copyWith(color: AppColors.textTertiary)),
                  const SizedBox(height: 8),
                  Text('Mulai bagikan resep favoritmu!',
                      style: Theme.of(context).textTheme.bodyMedium),
                ],
              ),
            );
          }

          // Hitung ringkasan
          final approved = list.where((r) => r.isApproved).length;
          final pending = list.where((r) => !r.isApproved).length;

          return CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              // ── Ringkasan Stats ──────────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    children: [
                      Expanded(
                        child: _StatBox(
                          value: list.length.toString(),
                          label: 'Total Resep',
                          color: AppColors.primary,
                          icon: Icons.restaurant_menu_rounded,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _StatBox(
                          value: approved.toString(),
                          label: 'Disetujui',
                          color: AppColors.success,
                          icon: Icons.check_circle_rounded,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _StatBox(
                          value: pending.toString(),
                          label: 'Pending',
                          color: AppColors.warning,
                          icon: Icons.pending_rounded,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // ── Filter info ──────────────────────────────────
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                  child: Text('Semua Resep Anda',
                      style: Theme.of(context).textTheme.headlineMedium),
                ),
              ),

              // ── List Resep ───────────────────────────────────
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (ctx, i) => _MyRecipeCard(recipe: list[i]),
                  childCount: list.length,
                ),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: 80)),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

// ── My Recipe Card ─────────────────────────────────────────────────────────────
class _MyRecipeCard extends StatelessWidget {
  final RecipeModel recipe;
  const _MyRecipeCard({required this.recipe});

  @override
  Widget build(BuildContext context) {
    final isApproved = recipe.isApproved;

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 0, 20, 12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 10)
        ],
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: isApproved
            ? () => context.push('/recipe/${recipe.id}')
            : null,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Thumbnail
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: recipe.imageUrl != null
                    ? CachedNetworkImage(
                        imageUrl: recipe.imageUrl!,
                        width: 72,
                        height: 72,
                        fit: BoxFit.cover,
                        errorWidget: (c, u, e) => _PlaceholderImg(),
                      )
                    : _PlaceholderImg(),
              ),
              const SizedBox(width: 14),

              // Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      recipe.title,
                      style: GoogleFonts.playfairDisplay(
                          fontSize: 15, fontWeight: FontWeight.w700),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${recipe.difficulty} · ${recipe.categoryName ?? ""}',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                    const SizedBox(height: 8),

                    // Status badge
                    _StatusBadge(isApproved: isApproved),
                  ],
                ),
              ),

              // Arrow jika approved
              if (isApproved)
                const Icon(Icons.arrow_forward_ios_rounded,
                    size: 14, color: AppColors.textDisabled),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final bool isApproved;
  const _StatusBadge({required this.isApproved});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: isApproved
            ? AppColors.success.withOpacity(0.12)
            : AppColors.warning.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isApproved
              ? AppColors.success.withOpacity(0.4)
              : AppColors.warning.withOpacity(0.4),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isApproved
                ? Icons.check_circle_rounded
                : Icons.access_time_rounded,
            size: 13,
            color: isApproved ? AppColors.success : AppColors.warning,
          ),
          const SizedBox(width: 5),
          Text(
            isApproved ? 'Disetujui' : 'Menunggu Review',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: isApproved ? AppColors.success : AppColors.warning,
            ),
          ),
        ],
      ),
    );
  }
}

class _StatBox extends StatelessWidget {
  final String value;
  final String label;
  final Color color;
  final IconData icon;

  const _StatBox(
      {required this.value,
      required this.label,
      required this.color,
      required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 6)
        ],
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 6),
          Text(value,
              style: GoogleFonts.playfairDisplay(
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  color: AppColors.textPrimary)),
          const SizedBox(height: 2),
          Text(label,
              style: Theme.of(context)
                  .textTheme
                  .bodySmall
                  ?.copyWith(fontSize: 10),
              textAlign: TextAlign.center),
        ],
      ),
    );
  }
}

class _PlaceholderImg extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 72,
      height: 72,
      decoration: BoxDecoration(
        color: AppColors.surfaceVariant,
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Icon(Icons.restaurant_menu_rounded,
          color: AppColors.border, size: 28),
    );
  }
}
