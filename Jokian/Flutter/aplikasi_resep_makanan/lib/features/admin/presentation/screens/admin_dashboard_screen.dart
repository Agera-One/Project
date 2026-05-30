// lib/features/admin/presentation/screens/admin_dashboard_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';

class AdminDashboardScreen extends ConsumerWidget {
  const AdminDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profile = ref.watch(currentProfileProvider);
    final stats = ref.watch(adminStatsProvider);

    final displayName = profile.value?.displayName ?? 'Admin';
    final initial = displayName.isNotEmpty ? displayName[0].toUpperCase() : 'A';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(),
          slivers: [
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
                // FIX 1: Tap avatar → halaman profil admin
                Padding(
                  padding: const EdgeInsets.only(right: 16),
                  child: GestureDetector(
                    onTap: () => context.push(AppRoutes.adminProfile),
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

            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('Selamat datang, $displayName! 👋',
                      style: Theme.of(context)
                          .textTheme
                          .bodyMedium
                          ?.copyWith(color: AppColors.textTertiary)),
                  const SizedBox(height: 4),
                  Text('Admin Dashboard',
                      style: Theme.of(context).textTheme.displaySmall),
                  const SizedBox(height: 24),

                  // Stats Grid
                  stats.when(
                    data: (s) => GridView.count(
                      crossAxisCount: 2, shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      mainAxisSpacing: 14, crossAxisSpacing: 14,
                      childAspectRatio: 1.4,
                      children: [
                        _StatCard(icon: Icons.restaurant_menu_rounded, label: 'Total Resep',
                            value: s.totalRecipes.toString(), color: AppColors.primary),
                        _StatCard(icon: Icons.pending_rounded, label: 'Menunggu Review',
                            value: s.pendingCount.toString(), color: AppColors.warning),
                        _StatCard(icon: Icons.people_rounded, label: 'Pengguna Aktif',
                            value: s.activeUsers.toString(), color: AppColors.info),
                        _StatCard(icon: Icons.star_rounded, label: 'Resep Unggulan',
                            value: s.featuredCount.toString(), color: AppColors.catDessert),
                      ],
                    ),
                    loading: () => GridView.count(
                      crossAxisCount: 2, shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      mainAxisSpacing: 14, crossAxisSpacing: 14, childAspectRatio: 1.4,
                      children: List.generate(4, (_) => Container(
                        decoration: BoxDecoration(
                            color: AppColors.surfaceVariant,
                            borderRadius: BorderRadius.circular(20)),
                      )),
                    ),
                    error: (e, _) => Text('Gagal: $e'),
                  ),

                  const SizedBox(height: 28),
                  Text('Aksi Cepat', style: Theme.of(context).textTheme.headlineMedium),
                  const SizedBox(height: 14),

                  _ActionCard(
                    icon: Icons.pending_rounded,
                    title: 'Review Resep Baru',
                    subtitle: 'Tinjau dan setujui resep yang dikirim pengguna',
                    color: AppColors.warning,
                    onTap: () => context.go(AppRoutes.adminReview),
                    // FIX 4: Refresh stats setelah masuk review
                  ),
                  const SizedBox(height: 12),
                  _ActionCard(
                    icon: Icons.restaurant_menu_rounded,
                    title: 'Kelola Semua Resep',
                    subtitle: 'Edit, hapus, atau atur resep unggulan',
                    color: AppColors.primary,
                    onTap: () => context.go(AppRoutes.adminRecipes),
                  ),
                  const SizedBox(height: 12),
                  _ActionCard(
                    icon: Icons.person_outline_rounded,
                    title: 'Profil Saya',
                    subtitle: 'Lihat dan edit informasi akun admin',
                    color: AppColors.info,
                    onTap: () => context.push(AppRoutes.adminProfile),
                  ),
                  const SizedBox(height: 12),
                  _ActionCard(
                    icon: Icons.logout_rounded,
                    title: 'Keluar',
                    subtitle: 'Sign out dari akun admin',
                    color: AppColors.error,
                    onTap: () async {
                      await ref.read(authNotifierProvider.notifier).signOut();
                      if (context.mounted) context.go(AppRoutes.login);
                    },
                  ),
                  const SizedBox(height: 20),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String label, value;
  final Color color;
  const _StatCard({required this.icon, required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: AppColors.surface, borderRadius: BorderRadius.circular(20),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 3))],
    ),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Container(
        width: 40, height: 40,
        decoration: BoxDecoration(color: color.withOpacity(0.12), borderRadius: BorderRadius.circular(12)),
        child: Icon(icon, color: color, size: 20),
      ),
      const Spacer(),
      Text(value,
          style: GoogleFonts.playfairDisplay(fontSize: 28, fontWeight: FontWeight.w900, color: AppColors.textPrimary)),
      const SizedBox(height: 2),
      Text(label, style: Theme.of(context).textTheme.bodySmall?.copyWith(fontSize: 11)),
    ]),
  );
}

class _ActionCard extends StatelessWidget {
  final IconData icon;
  final String title, subtitle;
  final Color color;
  final VoidCallback onTap;
  const _ActionCard({required this.icon, required this.title, required this.subtitle,
      required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface, borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8)],
      ),
      child: Row(children: [
        Container(
          width: 44, height: 44,
          decoration: BoxDecoration(color: color.withOpacity(0.12), borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: color, size: 22),
        ),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 2),
          Text(subtitle, style: Theme.of(context).textTheme.bodySmall),
        ])),
        const Icon(Icons.arrow_forward_ios_rounded, size: 16, color: AppColors.textDisabled),
      ]),
    ),
  );
}
