// lib/shared/widgets/recipe_card.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:shimmer/shimmer.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';
import 'package:resepku/features/bookmark/presentation/providers/bookmark_provider.dart';

// ── Recipe Card Large ─────────────────────────────────────────────────────────
class RecipeCardLarge extends ConsumerWidget {
  final RecipeModel recipe;
  final VoidCallback? onTap;

  const RecipeCardLarge({
    super.key,
    required this.recipe,
    this.onTap,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final bookmarks = ref.watch(bookmarkNotifierProvider);
    final isBookmarked = bookmarks.value?.contains(recipe.id) ?? false;

    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: onTap ?? () => context.push('/recipe/${recipe.id}'),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image
              Stack(
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(
                      top: Radius.circular(20),
                    ),
                    child: recipe.imageUrl != null
                        ? CachedNetworkImage(
                            imageUrl: recipe.imageUrl!,
                            height: 200,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => _ImageShimmer(),
                            errorWidget: (context, url, error) =>
                                _ImagePlaceholder(),
                          )
                        : _ImagePlaceholder(),
                  ),
                  // Difficulty tag
                  Positioned(
                    bottom: 12,
                    left: 12,
                    child: _DifficultyBadge(difficulty: recipe.difficulty),
                  ),
                  // Bookmark button
                  Positioned(
                    top: 12,
                    right: 12,
                    child: _BookmarkButton(
                      recipeId: recipe.id,
                      isBookmarked: isBookmarked,
                      onTap: () => ref
                          .read(bookmarkNotifierProvider.notifier)
                          .toggle(recipe.id),
                    ),
                  ),
                ],
              ),
              // Info
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      recipe.title,
                      style: Theme.of(context).textTheme.headlineSmall,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.access_time_rounded,
                            size: 14, color: AppColors.textTertiary),
                        const SizedBox(width: 4),
                        Text(
                          recipe.cookingTime,
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                        const SizedBox(width: 12),
                        const Icon(Icons.star_rounded,
                            size: 14, color: Color(0xFFFFC107)),
                        const SizedBox(width: 4),
                        Text(
                          recipe.rating.toStringAsFixed(1),
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                        if (recipe.categoryName != null) ...[
                          const SizedBox(width: 12),
                          const Icon(Icons.category_outlined,
                              size: 14, color: AppColors.textTertiary),
                          const SizedBox(width: 4),
                          Text(
                            recipe.categoryName!,
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Recipe Card Small (Grid) ───────────────────────────────────────────────────
class RecipeCardSmall extends ConsumerWidget {
  final RecipeModel recipe;

  const RecipeCardSmall({super.key, required this.recipe});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: () => context.push('/recipe/${recipe.id}'),
        child: Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.07),
                blurRadius: 12,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(16),
                ),
                child: recipe.imageUrl != null
                    ? CachedNetworkImage(
                        imageUrl: recipe.imageUrl!,
                        height: 120,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => _ImageShimmer(height: 120),
                        errorWidget: (context, url, error) =>
                            _ImagePlaceholder(height: 120),
                      )
                    : _ImagePlaceholder(height: 120),
              ),
              Padding(
                padding: const EdgeInsets.all(10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      recipe.title,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontSize: 13,
                          ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '⏱ ${recipe.cookingTime} · ⭐ ${recipe.rating.toStringAsFixed(1)}',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Featured Card ─────────────────────────────────────────────────────────────
class FeaturedRecipeCard extends ConsumerWidget {
  final RecipeModel recipe;

  const FeaturedRecipeCard({super.key, required this.recipe});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: () => context.push('/recipe/${recipe.id}'),
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 20),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero Image
              ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(20),
                ),
                child: recipe.imageUrl != null
                    ? CachedNetworkImage(
                        imageUrl: recipe.imageUrl!,
                        height: 180,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => _ImageShimmer(height: 180),
                        errorWidget: (context, url, error) =>
                            _ImagePlaceholder(height: 180),
                      )
                    : _ImagePlaceholder(height: 180),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Tags
                    Row(
                      children: [
                        _Tag(label: recipe.categoryName ?? 'Diet Sehat'),
                        const SizedBox(width: 8),
                        _Tag(
                            label: '• Populer Pekan Ini',
                            color: AppColors.warning),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      recipe.title,
                      style: Theme.of(context).textTheme.displaySmall?.copyWith(
                            fontSize: 22,
                          ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      recipe.description ?? '',
                      style: Theme.of(context).textTheme.bodyMedium,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        _StatChip(
                            icon: Icons.people_outline,
                            label: '2 Orang'),
                        const SizedBox(width: 16),
                        _StatChip(
                            icon: Icons.list_alt_rounded,
                            label: '${recipe.ingredients.length} Bahan'),
                        const Spacer(),
                        ElevatedButton(
                          onPressed: () =>
                              context.push('/recipe/${recipe.id}'),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 20, vertical: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: const Text('Lihat Resep'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Helper Widgets ─────────────────────────────────────────────────────────────
class _ImageShimmer extends StatelessWidget {
  final double height;
  const _ImageShimmer({this.height = 200});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: const Color(0xFFE8DDD0),
      highlightColor: const Color(0xFFF5EFE8),
      child: Container(
        height: height,
        width: double.infinity,
        color: const Color(0xFFE8DDD0),
      ),
    );
  }
}

class _ImagePlaceholder extends StatelessWidget {
  final double height;
  const _ImagePlaceholder({this.height = 200});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: height,
      width: double.infinity,
      color: AppColors.surfaceVariant,
      child: const Icon(
        Icons.restaurant_menu_rounded,
        size: 48,
        color: AppColors.border,
      ),
    );
  }
}

class _DifficultyBadge extends StatelessWidget {
  final String difficulty;
  const _DifficultyBadge({required this.difficulty});

  Color get _color {
    switch (difficulty) {
      case 'Mudah':
        return AppColors.success;
      case 'Medium':
        return AppColors.warning;
      case 'Sulit':
        return AppColors.error;
      default:
        return AppColors.primary;
    }
  }

  String get _icon {
    switch (difficulty) {
      case 'Mudah':
        return '⚡';
      case 'Medium':
        return '📈';
      case 'Sulit':
        return '🔥';
      default:
        return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration: BoxDecoration(
        color: _color,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        '$_icon $difficulty',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _BookmarkButton extends StatelessWidget {
  final String recipeId;
  final bool isBookmarked;
  final VoidCallback onTap;

  const _BookmarkButton({
    required this.recipeId,
    required this.isBookmarked,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.15),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Icon(
            isBookmarked ? Icons.bookmark_rounded : Icons.bookmark_border_rounded,
            size: 18,
            color: isBookmarked ? AppColors.primary : AppColors.textTertiary,
          ),
        ),
      ),
    );
  }
}

class _Tag extends StatelessWidget {
  final String label;
  final Color? color;
  const _Tag({required this.label, this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: (color ?? AppColors.primary).withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: (color ?? AppColors.primary).withOpacity(0.3),
        ),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color ?? AppColors.primary,
        ),
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  final IconData icon;
  final String label;
  const _StatChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 14, color: AppColors.textTertiary),
        const SizedBox(width: 4),
        Text(label, style: Theme.of(context).textTheme.bodySmall),
      ],
    );
  }
}

// ── Shimmer Card Placeholder ──────────────────────────────────────────────────
class RecipeCardShimmer extends StatelessWidget {
  const RecipeCardShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: const Color(0xFFE8DDD0),
      highlightColor: const Color(0xFFF5EFE8),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
        height: 280,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
        ),
      ),
    );
  }
}
