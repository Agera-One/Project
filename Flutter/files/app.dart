// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'providers/documents_provider.dart';
import 'screens/dashboard_screen.dart';
import 'screens/documents_screens.dart';
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

class AppShell extends ConsumerWidget {
  const AppShell({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentIndex = ref.watch(currentScreenProvider);

    final screens = [
      const DashboardScreen(),
      const RecentlyScreen(),
      const StarredScreen(),
      const MyDocumentsScreen(),
      const ArchivesScreen(),
      const TrashScreen(),
    ];

    final screenTitles = [
      'Dashboard',
      'Recently',
      'Starred',
      'My Documents',
      'Archives',
      'Trash',
    ];

    return Scaffold(
      backgroundColor: kBgColor,
      drawer: const AppDrawer(),
      // AppBar only for dashboard (other screens have their own header)
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
      floatingActionButton: const UploadFab(),
      bottomNavigationBar: _BottomNav(
        currentIndex: currentIndex,
        onTap: (index) =>
            ref.read(currentScreenProvider.notifier).state = index,
      ),
    );
  }
}

class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _BottomNav({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: kSurfaceColor,
        border: Border(top: BorderSide(color: kDividerColor, width: 1)),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _BottomNavItem(
                  icon: Icons.grid_view_rounded,
                  label: 'Home',
                  isActive: currentIndex == 0,
                  onTap: () => onTap(0)),
              _BottomNavItem(
                  icon: Icons.description_outlined,
                  label: 'Docs',
                  isActive: currentIndex == 3,
                  onTap: () => onTap(3)),
              const SizedBox(width: 56), // FAB space
              _BottomNavItem(
                  icon: Icons.star_outline_rounded,
                  label: 'Starred',
                  isActive: currentIndex == 2,
                  onTap: () => onTap(2)),
              _BottomNavItem(
                  icon: Icons.inventory_2_outlined,
                  label: 'Archive',
                  isActive: currentIndex == 4,
                  onTap: () => onTap(4)),
            ],
          ),
        ),
      ),
    );
  }
}

class _BottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _BottomNavItem({
    required this.icon,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              color: isActive ? kAccentGreen : kTextTertiary,
              size: 22,
            ),
            const SizedBox(height: 3),
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
