// lib/features/recipes/presentation/screens/recipe_detail_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/bookmark/presentation/providers/bookmark_provider.dart';
import 'package:resepku/features/recipes/data/models/recipe_model.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';

class RecipeDetailScreen extends ConsumerStatefulWidget {
  final String recipeId;
  const RecipeDetailScreen({super.key, required this.recipeId});

  @override
  ConsumerState<RecipeDetailScreen> createState() => _RecipeDetailScreenState();
}

class _RecipeDetailScreenState extends ConsumerState<RecipeDetailScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final Set<int> _checkedIngredients = {};
  bool _isCookingMode = false;
  int _cookingStep = 0;

  // Nama default untuk setiap langkah memasak
  final List<String> _defaultStepNames = [
    'Persiapan Bahan',
    'Mulai Memasak',
    'Proses Utama',
    'Bumbu & Rasa',
    'Finishing',
    'Penyajian',
    'Selesai',
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    debugPrint('[RecipeDetailScreen] Opening recipeId=${widget.recipeId}');
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final recipeAsync = ref.watch(recipeDetailProvider(widget.recipeId));
    final bookmarks = ref.watch(bookmarkNotifierProvider);

    return recipeAsync.when(
      data: (recipe) {
        if (recipe == null) {
          debugPrint('[RecipeDetailScreen] Recipe is null for id=${widget.recipeId}');
          return Scaffold(
            body: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: AppColors.error),
                  const SizedBox(height: 16),
                  const Text('Resep tidak ditemukan'),
                  const SizedBox(height: 8),
                  ElevatedButton(
                    onPressed: () => context.pop(),
                    child: const Text('Kembali'),
                  ),
                ],
              ),
            ),
          );
        }

        debugPrint('[RecipeDetailScreen] Rendering: ${recipe.title}');
        debugPrint('  ingredients: ${recipe.ingredients.length}');
        debugPrint('  steps: ${recipe.steps.length}');
        debugPrint('  isCookingMode: $_isCookingMode');

        final isBookmarked = bookmarks.value?.contains(recipe.id) ?? false;

        // BUG FIX: Cooking mode hanya muncul jika _isCookingMode = true
        // dan steps tidak boleh kosong
        if (_isCookingMode && recipe.steps.isNotEmpty) {
          return _buildCookingMode(recipe);
        }

        // Halaman detail utama
        return _buildDetailPage(recipe, isBookmarked);
      },
      loading: () => const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      ),
      error: (e, stack) {
        debugPrint('[RecipeDetailScreen] ERROR: $e\n$stack');
        return Scaffold(
          body: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 64, color: AppColors.error),
                const SizedBox(height: 16),
                Text('Gagal memuat resep', style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 8),
                Text('$e', style: Theme.of(context).textTheme.bodySmall, textAlign: TextAlign.center),
                const SizedBox(height: 16),
                ElevatedButton(onPressed: () => context.pop(), child: const Text('Kembali')),
              ],
            ),
          ),
        );
      },
    );
  }

  // ── Detail Page ─────────────────────────────────────────────────────────────
  // BUG FIX: Pisahkan halaman detail dan cooking mode menjadi method terpisah.
  // Sebelumnya SliverFillRemaining + TabBarView tanpa bounded height
  // menyebabkan konten tidak ter-render.
  // Solusi: gunakan DefaultTabController + NestedScrollView yang proper.
  Widget _buildDetailPage(RecipeModel recipe, bool isBookmarked) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          // ── Hero Image ────────────────────────────────────────
          SliverAppBar(
            expandedHeight: 280,
            pinned: true,
            backgroundColor: AppColors.background,
            leading: Padding(
              padding: const EdgeInsets.only(left: 12),
              child: _CircleBtn(
                icon: Icons.arrow_back_rounded,
                onTap: () {
                  if (context.canPop()) {
                    context.pop();
                  } else {
                    context.go('/');
                  }
                },
              ),
            ),
            actions: [
              _CircleBtn(icon: Icons.share_outlined, onTap: () {}),
              const SizedBox(width: 4),
              _CircleBtn(
                icon: isBookmarked
                    ? Icons.bookmark_rounded
                    : Icons.bookmark_border_rounded,
                iconColor: isBookmarked ? AppColors.primary : null,
                onTap: () {
                  debugPrint('[RecipeDetailScreen] Toggle bookmark for ${recipe.id}');
                  ref
                      .read(bookmarkNotifierProvider.notifier)
                      .toggle(recipe.id);
                },
              ),
              const SizedBox(width: 12),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  recipe.imageUrl != null
                      ? CachedNetworkImage(
                          imageUrl: recipe.imageUrl!,
                          fit: BoxFit.cover,
                          placeholder: (c, u) =>
                              Container(color: AppColors.surfaceVariant),
                          errorWidget: (c, u, e) => Container(
                            color: AppColors.surfaceVariant,
                            child: const Icon(Icons.restaurant_menu_rounded,
                                size: 64, color: AppColors.border),
                          ),
                        )
                      : Container(
                          color: AppColors.surfaceVariant,
                          child: const Icon(Icons.restaurant_menu_rounded,
                              size: 64, color: AppColors.border),
                        ),
                  DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.black.withOpacity(0.3),
                          Colors.transparent,
                          Colors.black.withOpacity(0.3),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── Info Header ───────────────────────────────────────
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Tags
                  Wrap(
                    spacing: 8,
                    runSpacing: 6,
                    children: [
                      _DetailTag(
                          label: recipe.categoryName?.toUpperCase() ?? 'RESEP'),
                      _DetailTag(label: recipe.cookingTime.toUpperCase()),
                      _DetailTag(label: recipe.difficulty.toUpperCase()),
                    ],
                  ),
                  const SizedBox(height: 14),
                  // Title
                  Text(
                    recipe.title,
                    style: Theme.of(context)
                        .textTheme
                        .displaySmall
                        ?.copyWith(fontSize: 26),
                  ),
                  const SizedBox(height: 10),
                  // Description
                  if (recipe.description != null &&
                      recipe.description!.isNotEmpty)
                    Text(
                      recipe.description!,
                      style: Theme.of(context)
                          .textTheme
                          .bodyLarge
                          ?.copyWith(height: 1.65),
                    ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),

          // BUG FIX: TabBar diletakkan di SliverPersistentHeader agar selalu
          // terlihat saat scroll, dan tidak menggunakan SliverFillRemaining
          SliverPersistentHeader(
            pinned: true,
            delegate: _TabBarDelegate(
              TabBar(
                controller: _tabController,
                labelColor: AppColors.primary,
                unselectedLabelColor: AppColors.textDisabled,
                indicatorColor: AppColors.primary,
                indicatorWeight: 2,
                labelStyle:
                    GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w600),
                unselectedLabelStyle:
                    GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w500),
                tabs: const [
                  Tab(text: 'Ingredients'),
                  Tab(text: 'Langkah Memasak'),
                ],
              ),
            ),
          ),
        ],

        // BUG FIX: Body adalah TabBarView dengan ukuran yang proper
        body: TabBarView(
          controller: _tabController,
          children: [
            // Tab 1: Ingredients
            _buildIngredients(recipe),
            // Tab 2: Steps
            _buildSteps(recipe),
          ],
        ),
      ),

      // ── Bottom Button ──────────────────────────────────────────
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
          child: ElevatedButton.icon(
            onPressed: recipe.steps.isEmpty
                ? null
                : () {
                    debugPrint('[RecipeDetailScreen] Starting cooking mode');
                    setState(() {
                      _isCookingMode = true;
                      _cookingStep = 0;
                    });
                  },
            icon: const Icon(Icons.play_circle_outline_rounded),
            label: Text(
              recipe.steps.isEmpty ? 'Belum ada langkah memasak' : 'START COOKING MODE',
            ),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
            ),
          ),
        ),
      ),
    );
  }

  // ── Ingredients Tab ─────────────────────────────────────────────────────────
  Widget _buildIngredients(RecipeModel recipe) {
    if (recipe.ingredients.isEmpty) {
      return const Center(
        child: Text('Belum ada daftar bahan.',
            style: TextStyle(color: AppColors.textTertiary)),
      );
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      children: [
        // Checklist header
        Row(children: [
          const Text('🧺', style: TextStyle(fontSize: 20)),
          const SizedBox(width: 8),
          Text('Daftar Bahan',
              style: Theme.of(context)
                  .textTheme
                  .titleLarge
                  ?.copyWith(fontSize: 16)),
        ]),
        const SizedBox(height: 12),

        // Checklist items
        ...recipe.ingredients.asMap().entries.map((entry) {
          final i = entry.key;
          final item = entry.value;
          final checked = _checkedIngredients.contains(i);
          return GestureDetector(
            onTap: () => setState(() {
              if (checked) {
                _checkedIngredients.remove(i);
              } else {
                _checkedIngredients.add(i);
              }
            }),
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 12),
              decoration: const BoxDecoration(
                  border: Border(
                      bottom: BorderSide(color: AppColors.borderLight))),
              child: Row(children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 22,
                  height: 22,
                  decoration: BoxDecoration(
                    color: checked ? AppColors.primary : Colors.transparent,
                    border: Border.all(
                        color: checked ? AppColors.primary : AppColors.border,
                        width: 2),
                    borderRadius: BorderRadius.circular(5),
                  ),
                  child: checked
                      ? const Icon(Icons.check_rounded,
                          size: 14, color: Colors.white)
                      : null,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    item,
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                          decoration:
                              checked ? TextDecoration.lineThrough : null,
                          color: checked
                              ? AppColors.textDisabled
                              : AppColors.textSecondary,
                        ),
                  ),
                ),
              ]),
            ),
          );
        }),

        const SizedBox(height: 20),

        // Chef's Tip
        Container(
          padding: const EdgeInsets.all(16),
          decoration: const BoxDecoration(
            color: AppColors.primaryContainer,
            border: Border(
                left: BorderSide(color: AppColors.primary, width: 3)),
            borderRadius: BorderRadius.only(
              topRight: Radius.circular(12),
              bottomRight: Radius.circular(12),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Chef\'s Tip',
                  style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primary)),
              const SizedBox(height: 4),
              Text(
                'Pastikan semua bahan dalam suhu ruang sebelum mulai memasak untuk hasil yang optimal.',
                style: Theme.of(context)
                    .textTheme
                    .bodyMedium
                    ?.copyWith(height: 1.55),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // ── Steps Tab ───────────────────────────────────────────────────────────────
  Widget _buildSteps(RecipeModel recipe) {
    if (recipe.steps.isEmpty) {
      return const Center(
        child: Text('Belum ada langkah memasak.',
            style: TextStyle(color: AppColors.textTertiary)),
      );
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      children: [
        Text('Langkah-Langkah',
            style: Theme.of(context).textTheme.headlineLarge),
        const SizedBox(height: 16),
        ...recipe.steps.asMap().entries.map((entry) {
          final i = entry.key;
          final step = entry.value;
          final stepName = (step['name'] != null && step['name']!.isNotEmpty)
              ? step['name']!
              : (_defaultStepNames.length > i
                  ? _defaultStepNames[i]
                  : 'Langkah ${i + 1}');
          final stepDesc = step['description'] ?? '';

          return Padding(
            padding: const EdgeInsets.only(bottom: 20),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: const BoxDecoration(
                      color: AppColors.primary, shape: BoxShape.circle),
                  child: Center(
                    child: Text('${i + 1}',
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 14,
                            fontWeight: FontWeight.w700)),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(stepName,
                          style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 4),
                      Text(stepDesc,
                          style: Theme.of(context)
                              .textTheme
                              .bodyLarge
                              ?.copyWith(height: 1.6)),
                    ],
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }

  // ── Cooking Mode ─────────────────────────────────────────────────────────────
  Widget _buildCookingMode(RecipeModel recipe) {
    final steps = recipe.steps;
    if (steps.isEmpty) {
      // Fallback jika steps kosong
      setState(() => _isCookingMode = false);
      return const SizedBox.shrink();
    }

    final step = steps[_cookingStep];
    final stepName = (step['name'] != null && step['name']!.isNotEmpty)
        ? step['name']!
        : (_defaultStepNames.length > _cookingStep
            ? _defaultStepNames[_cookingStep]
            : 'Langkah ${_cookingStep + 1}');

    return Scaffold(
      backgroundColor: AppColors.primary,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  GestureDetector(
                    onTap: () {
                      debugPrint('[CookingMode] Exit cooking mode');
                      setState(() => _isCookingMode = false);
                    },
                    child: const Icon(Icons.close_rounded,
                        color: Colors.white, size: 28),
                  ),
                  Text(
                    'Langkah ${_cookingStep + 1} dari ${steps.length}',
                    style: const TextStyle(
                        color: Colors.white70,
                        fontSize: 14,
                        fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(width: 28),
                ],
              ),
              const SizedBox(height: 12),
              LinearProgressIndicator(
                value: (_cookingStep + 1) / steps.length,
                backgroundColor: Colors.white24,
                valueColor:
                    const AlwaysStoppedAnimation<Color>(Colors.white),
                borderRadius: BorderRadius.circular(4),
              ),
              const Spacer(),
              Text(
                'Langkah ${_cookingStep + 1}',
                style: const TextStyle(
                    color: Colors.white54,
                    fontSize: 14,
                    letterSpacing: 2),
              ),
              const SizedBox(height: 12),
              Text(
                stepName,
                style: GoogleFonts.playfairDisplay(
                    fontSize: 32,
                    fontWeight: FontWeight.w700,
                    color: Colors.white),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  step['description'] ?? '',
                  style: const TextStyle(
                      color: Colors.white, fontSize: 16, height: 1.7),
                  textAlign: TextAlign.center,
                ),
              ),
              const Spacer(),
              Row(
                children: [
                  if (_cookingStep > 0)
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () =>
                            setState(() => _cookingStep--),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: const BorderSide(color: Colors.white54),
                          padding:
                              const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14)),
                        ),
                        child: const Text('← Sebelumnya'),
                      ),
                    ),
                  if (_cookingStep > 0) const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton(
                      onPressed: () {
                        if (_cookingStep < steps.length - 1) {
                          setState(() => _cookingStep++);
                        } else {
                          setState(() => _isCookingMode = false);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: const Text(
                                  '🎉 Selamat! Masakan Anda siap!'),
                              backgroundColor: AppColors.success,
                              behavior: SnackBarBehavior.floating,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10)),
                            ),
                          );
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: AppColors.primary,
                        padding:
                            const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                      child: Text(
                        _cookingStep < steps.length - 1
                            ? 'Selanjutnya →'
                            : '🎉 Selesai!',
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }
}

// ── TabBar Delegate ────────────────────────────────────────────────────────────
// BUG FIX: Diperlukan agar TabBar bisa di-pin di dalam NestedScrollView
class _TabBarDelegate extends SliverPersistentHeaderDelegate {
  final TabBar tabBar;
  const _TabBarDelegate(this.tabBar);

  @override
  double get minExtent => tabBar.preferredSize.height + 1;
  @override
  double get maxExtent => tabBar.preferredSize.height + 1;

  @override
  Widget build(
      BuildContext context, double shrinkOffset, bool overlapsContent) {
    return Container(
      color: AppColors.background,
      child: Column(
        children: [
          tabBar,
          const Divider(height: 1, color: AppColors.borderLight),
        ],
      ),
    );
  }

  @override
  bool shouldRebuild(_TabBarDelegate oldDelegate) => false;
}

// ── Helper Widgets ─────────────────────────────────────────────────────────────
class _CircleBtn extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final Color? iconColor;

  const _CircleBtn({required this.icon, required this.onTap, this.iconColor});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 38,
        height: 38,
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.9),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.1), blurRadius: 8)
          ],
        ),
        child: Icon(icon,
            size: 18, color: iconColor ?? AppColors.textPrimary),
      ),
    );
  }
}

class _DetailTag extends StatelessWidget {
  final String label;
  const _DetailTag({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.primaryContainer,
        borderRadius: BorderRadius.circular(20),
        border:
            Border.all(color: AppColors.primary.withOpacity(0.3)),
      ),
      child: Text(
        label,
        style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: AppColors.primary,
            letterSpacing: 1),
      ),
    );
  }
}
