// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'providers/documents_provider.dart';
import 'screens/auth_screen.dart';
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
      home: const _AuthGate(),
    );
  }
}

// ─── Auth Gate ────────────────────────────────────────────────
// Menggunakan ConsumerStatefulWidget agar bisa listen perubahan
// auth secara real-time (login, logout, session restore).
class _AuthGate extends ConsumerStatefulWidget {
  const _AuthGate();

  @override
  ConsumerState<_AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends ConsumerState<_AuthGate> {
  bool _isLoading = true;
  bool _isLoggedIn = false;

  @override
  void initState() {
    super.initState();
    _checkInitialSession();
    _listenAuthChanges();
  }

  // Cek session yang sudah tersimpan secara synchronous.
  // Ini yang memastikan user tidak tembus ke dashboard
  // saat belum login.
  void _checkInitialSession() {
    final session = Supabase.instance.client.auth.currentSession;
    setState(() {
      _isLoggedIn = session != null && session.user != null;
      _isLoading = false;
    });
  }

  // Listen perubahan auth state (login / logout) secara real-time
  void _listenAuthChanges() {
    Supabase.instance.client.auth.onAuthStateChange.listen((data) {
      if (!mounted) return;
      final session = data.session;
      final isNowLoggedIn = session != null && session.user != null;

      setState(() => _isLoggedIn = isNowLoggedIn);

      if (isNowLoggedIn) {
        // Baru login → fetch dokumen
        ref.read(documentsProvider.notifier).fetchDocuments();
      } else {
        // Logout → bersihkan state dokumen
        ref.read(documentsProvider.notifier).resetState();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    // Tampilkan loading spinner saat cek session awal
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: kBgColor,
        body: Center(
          child: CircularProgressIndicator(color: kAccentGreen),
        ),
      );
    }

    // Routing berdasarkan status login
    return _isLoggedIn ? const AppShell() : const AuthScreen();
  }
}

// ─── Bottom Nav Index mapping ─────────────────────────────────
// 0=Dashboard  1=Recently  2=Search  3=MyDocs
// 4=Starred  5=Archives  6=Trash  (drawer only)

class AppShell extends ConsumerWidget {
  const AppShell({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentIndex = ref.watch(currentScreenProvider);

    final screens = [
      const DashboardScreen(),
      const RecentlyScreen(),
      const SearchScreen(),
      const MyDocumentsScreen(),
      const StarredScreen(),
      const ArchivesScreen(),
      const TrashScreen(),
    ];

    return Scaffold(
      backgroundColor: kBgColor,
      drawer: const AppDrawer(),

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

      floatingActionButton:
          currentIndex == 2 ? null : const UploadFab(),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,

      bottomNavigationBar: _BottomNav(
        currentIndex: currentIndex,
        onTap: (navIdx) {
          ref.read(currentScreenProvider.notifier).state = navIdx;
        },
      ),
    );
  }
}

// ─── Bottom Navigation Bar ────────────────────────────────────
class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _BottomNav({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
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

// ─── Regular Nav Item ─────────────────────────────────────────
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
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
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
                fontWeight:
                    isActive ? FontWeight.w600 : FontWeight.w400,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Special Search Nav Item ──────────────────────────────────
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
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
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
                isActive
                    ? Icons.search_rounded
                    : Icons.search_outlined,
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
                fontWeight:
                    isActive ? FontWeight.w600 : FontWeight.w400,
              ),
            ),
          ],
        ),
      ),
    );
  }
}