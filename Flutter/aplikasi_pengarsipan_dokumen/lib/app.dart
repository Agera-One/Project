// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'providers/documents_provider.dart';
import 'screens/dashboard_screen.dart';
import 'screens/documents_screens.dart';
import 'screens/search_screen.dart';
import 'theme.dart';
import 'widgets/app_drawer.dart';
import 'widgets/upload_fab.dart';

class DocumentArchiverApp extends StatelessWidget {
  const DocumentArchiverApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Document Archiver',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark,
      home: const AppShell(),
    );
  }
}

// ─── Bottom Nav Index mapping ─────────────────────────────────────────────────
// Bottom nav : 0=Home  1=Recent  2=Search  3=Docs
// Screen idx : 0=Dashboard  1=Recently  2=Search  3=MyDocs
//   (drawer-only screens: 4=Starred  5=Archives  6=Trash)

class AppShell extends ConsumerWidget {
  const AppShell({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentIndex = ref.watch(currentScreenProvider);

    final screens = [
      const DashboardScreen(),   // 0
      const RecentlyScreen(),    // 1
      const SearchScreen(),      // 2  ← baru
      const MyDocumentsScreen(), // 3
      const StarredScreen(),     // 4  (via drawer)
      const ArchivesScreen(),    // 5  (via drawer)
      const TrashScreen(),       // 6  (via drawer)
    ];

    // Apakah layar aktif termasuk 4 tab bottom nav?
    final isBottomNavScreen = currentIndex <= 3;

    return Scaffold(
      backgroundColor: kBgColor,
      drawer: const AppDrawer(),

      // AppBar hanya untuk Dashboard (index 0)
      appBar: currentIndex == 0
          ? AppBar(
              backgroundColor: kSurfaceColor,
              elevation: 0,
              leading: Builder(
                builder: (ctx) => IconButton(
                  icon: const Icon(Icons.menu_rounded, color: kTextPrimary),
                  onPressed: () => Scaffold.of(ctx).openDrawer(),
                ),
              ),
              title: Row(
                children: [
                  Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: kAccentGreen.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.folder_special_rounded,
                      color: kAccentGreen,
                      size: 16,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Document Archiver',
                    style: GoogleFonts.poppins(
                      color: kTextPrimary,
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(1),
                child: Container(height: 1, color: kDividerColor),
              ),
            )
          : null,

      body: IndexedStack(
        index: currentIndex,
        children: screens,
      ),

      // FAB tidak tampil di halaman Search
      floatingActionButton: currentIndex == 2 ? null : const UploadFab(),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,

      bottomNavigationBar: _BottomNav(
        currentIndex: currentIndex,
        onTap: (navIdx) {
          // navIdx 0→screen 0, 1→screen 1, 2→screen 2, 3→screen 3
          ref.read(currentScreenProvider.notifier).state = navIdx;
        },
      ),
    );
  }
}

// ─── Bottom Navigation Bar ────────────────────────────────────────────────────
class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _BottomNav({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    // Hanya 4 tab yang ada di bottom nav (index 0–3)
    // Jika user di drawer-only screen (4,5,6), tidak ada tab yang aktif
    final activeTab = currentIndex <= 3 ? currentIndex : -1;

    return Container(
      decoration: const BoxDecoration(
        color: kSurfaceColor,
        border: Border(top: BorderSide(color: kDividerColor, width: 1)),
      ),
      child: SafeArea(
        child: SizedBox(
          height: 62,
          child: Row(
            children: [
              _NavItem(
                icon: Icons.grid_view_outlined,
                activeIcon: Icons.grid_view_rounded,
                label: 'Home',
                isActive: activeTab == 0,
                onTap: () => onTap(0),
              ),
              _NavItem(
                icon: Icons.access_time_outlined,
                activeIcon: Icons.access_time_filled_rounded,
                label: 'Recent',
                isActive: activeTab == 1,
                onTap: () => onTap(1),
              ),
              _SearchNavItem(
                isActive: activeTab == 2,
                onTap: () => onTap(2),
              ),
              _NavItem(
                icon: Icons.description_outlined,
                activeIcon: Icons.description_rounded,
                label: 'Docs',
                isActive: activeTab == 3,
                onTap: () => onTap(3),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Regular Nav Item ─────────────────────────────────────────────────────────
class _NavItem extends StatelessWidget {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _NavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: isActive
                    ? kAccentGreen.withOpacity(0.12)
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Icon(
                isActive ? activeIcon : icon,
                color: isActive ? kAccentGreen : kTextTertiary,
                size: 22,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.poppins(
                color: isActive ? kAccentGreen : kTextTertiary,
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Special Search Nav Item ──────────────────────────────────────────────────
class _SearchNavItem extends StatelessWidget {
  final bool isActive;
  final VoidCallback onTap;

  const _SearchNavItem({required this.isActive, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                // Search pakai accent tersendiri saat aktif (lingkaran hijau)
                color: isActive
                    ? kAccentGreen.withOpacity(0.15)
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(20),
                border: isActive
                    ? Border.all(
                        color: kAccentGreen.withOpacity(0.4), width: 1)
                    : null,
              ),
              child: Icon(
                isActive ? Icons.search_rounded : Icons.search_outlined,
                color: isActive ? kAccentGreen : kTextTertiary,
                size: 22,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              'Search',
              style: GoogleFonts.poppins(
                color: isActive ? kAccentGreen : kTextTertiary,
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
