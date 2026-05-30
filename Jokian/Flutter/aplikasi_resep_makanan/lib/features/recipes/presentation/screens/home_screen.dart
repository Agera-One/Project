// lib/features/recipes/presentation/screens/home_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';
import 'package:resepku/shared/widgets/recipe_card.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  String? _selectedCategoryId;

  @override
  Widget build(BuildContext context) {
    final profile = ref.watch(currentProfileProvider);
    final categories = ref.watch(categoriesProvider);
    final recipes = ref.watch(recipesProvider(_selectedCategoryId));

    // FIX 5: Nama user dari DB, bukan hardcode "Chef"
    final displayName = profile.value?.displayName ?? '';
    final initial = displayName.isNotEmpty ? displayName[0].toUpperCase() : 'U';
    // Gunakan nama jika ada, fallback ke "Chef"
    final greeting = displayName.isNotEmpty ? displayName : 'Chef';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(),
          slivers: [
            // ── App Bar ──────────────────────────────────────────────
            SliverAppBar(
              floating: true,
              backgroundColor: AppColors.background.withOpacity(0.95),
              elevation: 0,
              scrolledUnderElevation: 0,
              automaticallyImplyLeading: false,
              title: Text('RESEPKU',
                  style: GoogleFonts.playfairDisplay(
                      fontSize: 22, fontWeight: FontWeight.w700, color: AppColors.primary)),
              actions: [
                // FIX 1: Avatar tap → halaman profil
                Padding(
                  padding: const EdgeInsets.only(right: 16),
                  child: GestureDetector(
                    onTap: () => context.push(AppRoutes.profile),
                    child: CircleAvatar(
                      radius: 18,
                      backgroundColor: AppColors.primaryContainer,
                      child: Text(initial,
                          style: const TextStyle(
                              color: AppColors.primary, fontWeight: FontWeight.w700)),
                    ),
                  ),
                ),
              ],
            ),

            // ── Hero Greeting ─────────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // FIX 5: Tampilkan nama asli dari database
                    Text(
                      'Selamat datang, $greeting! 👋',
                      style: Theme.of(context)
                          .textTheme
                          .bodyMedium
                          ?.copyWith(color: AppColors.textTertiary),
                    ),
                    const SizedBox(height: 4),
                    RichText(
                      text: TextSpan(
                        style: GoogleFonts.playfairDisplay(
                            fontSize: 30,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPrimary,
                            height: 1.2),
                        children: [
                          const TextSpan(text: 'Temukan Resep\n'),
                          TextSpan(text: 'Terbaik',
                              style: const TextStyle(color: AppColors.primary)),
                          const TextSpan(text: ' Anda'),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // ── Search Bar ────────────────────────────────────────────
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: GestureDetector(
                  onTap: () => context.go(AppRoutes.search),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 12,
                          offset: const Offset(0, 2))],
                    ),
                    child: Row(children: [
                      const Icon(Icons.search_rounded, color: AppColors.textDisabled, size: 20),
                      const SizedBox(width: 10),
                      Text('Cari resep hari ini...',
                          style: Theme.of(context)
                              .textTheme
                              .bodyMedium
                              ?.copyWith(color: AppColors.textDisabled)),
                    ]),
                  ),
                ),
              ),
            ),

            // ── Filter Chips ──────────────────────────────────────────
            SliverToBoxAdapter(
              child: SizedBox(
                height: 52,
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  scrollDirection: Axis.horizontal,
                  children: [
                    _Chip(label: '≡ Kesulitan'),
                    const SizedBox(width: 8),
                    _Chip(label: '⏱ Waktu Masak'),
                    const SizedBox(width: 8),
                    _Chip(label: '🍴 Porsi'),
                    const SizedBox(width: 8),
                    _Chip(label: '🌿 Diet'),
                  ],
                ),
              ),
            ),

            // ── Categories ────────────────────────────────────────────
            SliverToBoxAdapter(
              child: Column(children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 4, 20, 14),
                  child: Row(children: [
                    Text('Kategori Populer',
                        style: Theme.of(context).textTheme.headlineMedium),
                    const Spacer(),
                    Text('Lihat Semua',
                        style: const TextStyle(
                            color: AppColors.primary,
                            fontSize: 13,
                            fontWeight: FontWeight.w600)),
                  ]),
                ),
                categories.when(
                  data: (cats) => SizedBox(
                    height: 88,
                    child: ListView(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      scrollDirection: Axis.horizontal,
                      children: [
                        _CatItem(
                          emoji: '🍽️',
                          name: 'Semua',
                          isSelected: _selectedCategoryId == null,
                          color: AppColors.primary,
                          onTap: () => setState(() => _selectedCategoryId = null),
                        ),
                        const SizedBox(width: 16),
                        ...cats.map((cat) => Padding(
                          padding: const EdgeInsets.only(right: 16),
                          child: _CatItem(
                            emoji: cat.emoji,
                            name: cat.name
                                .replaceAll('Makanan ', '')
                                .replaceAll('Menu ', ''),
                            isSelected: _selectedCategoryId == cat.id,
                            color: cat.color,
                            onTap: () => setState(() =>
                                _selectedCategoryId =
                                    _selectedCategoryId == cat.id ? null : cat.id),
                          ),
                        )),
                      ],
                    ),
                  ),
                  loading: () => const SizedBox(
                      height: 88, child: Center(child: CircularProgressIndicator())),
                  error: (_, __) => const SizedBox.shrink(),
                ),
              ]),
            ),

            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 14),
                child: Text('Rekomendasi Untuk Anda',
                    style: Theme.of(context).textTheme.headlineMedium),
              ),
            ),

            // ── Recipes List ──────────────────────────────────────────
            recipes.when(
              data: (list) {
                debugPrint('[HomeScreen] Rendering ${list.length} recipes');
                if (list.isEmpty) {
                  return SliverToBoxAdapter(
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.all(40),
                        child: Column(children: [
                          const Text('🍽️', style: TextStyle(fontSize: 48)),
                          const SizedBox(height: 12),
                          Text('Belum ada resep di kategori ini',
                              style: Theme.of(context).textTheme.bodyLarge),
                        ]),
                      ),
                    ),
                  );
                }

                // BUG FIX: Pisahkan featured dan normal
                final featured = list.where((r) => r.isFeatured).toList();
                final normal = list.where((r) => !r.isFeatured).toList();

                // Bangun daftar widget secara eksplisit
                // sehingga SEMUA resep pasti ter-render
                final widgets = <Widget>[];

                // Tampilkan 2 resep normal pertama sebagai large card
                for (int i = 0; i < normal.length && i < 2; i++) {
                  widgets.add(RecipeCardLarge(recipe: normal[i]));
                }

                // Tampilkan featured cards
                for (final r in featured) {
                  widgets.add(Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: FeaturedRecipeCard(recipe: r),
                  ));
                }

                // Tampilkan sisa resep normal (index 2 dan seterusnya)
                for (int i = 2; i < normal.length; i++) {
                  widgets.add(RecipeCardLarge(recipe: normal[i]));
                }

                return SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (ctx, i) => widgets[i],
                    childCount: widgets.length,
                  ),
                );
              },
              loading: () => SliverList(
                delegate: SliverChildBuilderDelegate(
                    (_, __) => const RecipeCardShimmer(), childCount: 3)),
              error: (e, _) => SliverToBoxAdapter(
                child: Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(children: [
                      const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                      const SizedBox(height: 12),
                      Text('Gagal memuat resep', style: Theme.of(context).textTheme.bodyLarge),
                      ElevatedButton(
                          onPressed: () => ref.invalidate(recipesProvider(_selectedCategoryId)),
                          child: const Text('Coba Lagi')),
                    ]),
                  ),
                ),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 20)),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => context.push(AppRoutes.recipeForm),
        backgroundColor: AppColors.accent,
        foregroundColor: Colors.white,
        shape: const CircleBorder(),
        child: const Icon(Icons.add_rounded, size: 28),
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  const _Chip({required this.label});
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
    decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border, width: 1.5),
        borderRadius: BorderRadius.circular(20)),
    child: Text(label,
        style: Theme.of(context)
            .textTheme
            .bodySmall
            ?.copyWith(fontWeight: FontWeight.w500, color: AppColors.textSecondary)),
  );
}

class _CatItem extends StatelessWidget {
  final String emoji, name;
  final bool isSelected;
  final Color color;
  final VoidCallback onTap;
  const _CatItem(
      {required this.emoji, required this.name, required this.isSelected,
       required this.color, required this.onTap});
  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Column(children: [
      AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 56, height: 56,
        decoration: BoxDecoration(
            color: isSelected ? color : AppColors.primaryContainer,
            borderRadius: BorderRadius.circular(16)),
        child: Center(child: Text(emoji, style: const TextStyle(fontSize: 24))),
      ),
      const SizedBox(height: 6),
      Text(name,
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? color : AppColors.textTertiary)),
    ]),
  );
}
