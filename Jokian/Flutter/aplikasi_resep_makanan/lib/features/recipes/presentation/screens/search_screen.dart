// lib/features/recipes/presentation/screens/search_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:resepku/core/constants/app_constants.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';
import 'package:resepku/shared/widgets/recipe_card.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final _controller = TextEditingController();
  String? _selectedDifficulty;

  @override
  void initState() {
    super.initState();
    _controller.addListener(() {
      ref.read(searchQueryProvider.notifier).state = _controller.text;
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final results = ref.watch(searchResultsProvider);
    final query = ref.watch(searchQueryProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── AppBar ─────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 20, 0),
              child: Row(
                children: [
                  // FIX 3: Tombol kembali
                  GestureDetector(
                    onTap: () => context.go(AppRoutes.home),
                    child: Container(
                      width: 38, height: 38,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6)],
                      ),
                      child: const Icon(Icons.arrow_back_rounded, color: AppColors.textPrimary, size: 20),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text('Cari Resep', style: Theme.of(context).textTheme.displaySmall),
                ],
              ),
            ),

            // ── Search Input ────────────────────────────────────
            Padding(
              padding: const EdgeInsets.all(20),
              child: TextField(
                controller: _controller,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Cari resep hari ini...',
                  prefixIcon: const Icon(Icons.search_rounded, color: AppColors.textDisabled),
                  suffixIcon: _controller.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.close_rounded, color: AppColors.textTertiary),
                          onPressed: () {
                            _controller.clear();
                            ref.read(searchQueryProvider.notifier).state = '';
                          },
                        )
                      : null,
                ),
              ),
            ),

            // ── Difficulty Filter ──────────────────────────────
            SizedBox(
              height: 44,
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                scrollDirection: Axis.horizontal,
                children: [
                  _DiffChip(
                    label: 'Semua',
                    isSelected: _selectedDifficulty == null,
                    onTap: () {
                      setState(() => _selectedDifficulty = null);
                      ref.read(searchDifficultyProvider.notifier).state = null;
                    },
                  ),
                  const SizedBox(width: 8),
                  ...['Mudah', 'Medium', 'Sulit'].map((d) => Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: _DiffChip(
                          label: d,
                          isSelected: _selectedDifficulty == d,
                          onTap: () {
                            setState(() => _selectedDifficulty = d);
                            ref.read(searchDifficultyProvider.notifier).state = d;
                          },
                        ),
                      )),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // ── Results ─────────────────────────────────────────
            // BUG FIX: Selalu tampilkan hasil (semua resep saat query kosong)
            // Tidak ada lagi kondisi "tampilkan prompt kosong"
            Expanded(
              child: results.when(
                data: (list) {
                  debugPrint('[SearchScreen] Results: ${list.length}');
                  if (list.isEmpty && query.isNotEmpty) {
                    return _buildNoResults(context, query);
                  }
                  if (list.isEmpty) {
                    return const Center(
                      child: CircularProgressIndicator(),
                    );
                  }
                  return ListView.builder(
                    physics: const BouncingScrollPhysics(),
                    itemCount: list.length,
                    itemBuilder: (_, i) => RecipeCardLarge(recipe: list[i]),
                  );
                },
                loading: () => ListView.builder(
                  itemCount: 4,
                  itemBuilder: (_, __) => const RecipeCardShimmer(),
                ),
                error: (e, _) {
                  debugPrint('[SearchScreen] ERROR: $e');
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                        const SizedBox(height: 12),
                        Text('Gagal memuat resep', style: Theme.of(context).textTheme.bodyLarge),
                        const SizedBox(height: 8),
                        ElevatedButton(
                          onPressed: () => ref.invalidate(searchResultsProvider),
                          child: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyPrompt(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('🔍', style: TextStyle(fontSize: 56)),
          const SizedBox(height: 16),
          Text('Ketik untuk mencari resep...', style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: AppColors.textTertiary)),
          const SizedBox(height: 8),
          Text('Coba: "Ayam", "Mudah", "Dessert"', style: Theme.of(context).textTheme.bodyMedium),
        ],
      ),
    );
  }

  Widget _buildNoResults(BuildContext context, String query) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('😔', style: TextStyle(fontSize: 56)),
          const SizedBox(height: 16),
          Text('Resep "$query" tidak ditemukan', style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: AppColors.textTertiary), textAlign: TextAlign.center),
          const SizedBox(height: 8),
          Text('Coba kata kunci lain', style: Theme.of(context).textTheme.bodyMedium),
        ],
      ),
    );
  }
}

class _DiffChip extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _DiffChip({required this.label, required this.isSelected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary : AppColors.surface,
            border: Border.all(color: isSelected ? AppColors.primary : AppColors.border, width: 1.5),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: isSelected ? Colors.white : AppColors.textSecondary,
            ),
          ),
        ),
      ),
    );
  }
}
