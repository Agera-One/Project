// lib/widgets/app_drawer.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import '../providers/auth_provider.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';

// Provider to track current screen index
final currentScreenProvider = StateProvider<int>((ref) => 0);

class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentIndex = ref.watch(currentScreenProvider);
    final state = ref.watch(documentsProvider);
    final authState = ref.watch(authNotifierProvider);

    // Ambil data user dari Supabase Auth
    final user = Supabase.instance.client.auth.currentUser;
    final fullName = user?.userMetadata?['full_name'] as String? ??
        user?.email?.split('@').first ??
        'Pengguna';
    final email = user?.email ?? '';
    final initials = user?.userMetadata?['initials'] as String? ??
        _buildInitials(fullName);

    return Drawer(
      backgroundColor: kSurfaceColor,
      child: SafeArea(
        child: Column(
          children: [
            // ── Logo header ──────────────────────────────
            Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: kAccentGreen.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.folder_special_rounded,
                      color: kAccentGreen,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Document',
                        style: GoogleFonts.poppins(
                          color: kTextPrimary,
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          height: 1.2,
                        ),
                      ),
                      Text(
                        'Archiver',
                        style: GoogleFonts.poppins(
                          color: kAccentGreen,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 1.5,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const Divider(color: kDividerColor, height: 1),
            const SizedBox(height: 8),

            // ── Platform label ───────────────────────────
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'PLATFORM',
                  style: GoogleFonts.poppins(
                    color: kTextTertiary,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    letterSpacing: 1.5,
                  ),
                ),
              ),
            ),

            // ── Nav Items ────────────────────────────────
            _NavItem(
              icon: Icons.grid_view_rounded,
              label: 'Home',
              index: 0,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 0),
            ),
            _NavItem(
              icon: Icons.access_time_rounded,
              label: 'Recently',
              index: 1,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 1),
            ),
            _NavItem(
              icon: Icons.search_rounded,
              label: 'Search',
              index: 2,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 2),
            ),
            _NavItem(
              icon: Icons.description_outlined,
              label: 'My Documents',
              index: 3,
              badge: '${state.totalDocumentCount}',
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 3),
            ),
            _NavItem(
              icon: Icons.star_outline_rounded,
              label: 'Starred',
              index: 4,
              badge: state.starredCount > 0 ? '${state.starredCount}' : null,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 4),
            ),
            _NavItem(
              icon: Icons.inventory_2_outlined,
              label: 'Archives',
              index: 5,
              badge: state.archivedDocuments.isNotEmpty
                  ? '${state.archivedDocuments.length}'
                  : null,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 5),
            ),
            _NavItem(
              icon: Icons.delete_outline_rounded,
              label: 'Trash',
              index: 6,
              badge: state.trashedDocuments.isNotEmpty
                  ? '${state.trashedDocuments.length}'
                  : null,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 6),
            ),

            const Spacer(),
            const Divider(color: kDividerColor, height: 1),

            // ── User Profile + Logout ────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: [
                  // Avatar
                  Container(
                    width: 38,
                    height: 38,
                    decoration: const BoxDecoration(
                      color: kOwnerAvatarColor,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        initials,
                        style: GoogleFonts.poppins(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  // Name & email
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          fullName,
                          style: GoogleFonts.poppins(
                            color: kTextPrimary,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          email,
                          style: GoogleFonts.poppins(
                            color: kTextTertiary,
                            fontSize: 10,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // ── Logout Button ────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
              child: SizedBox(
                width: double.infinity,
                child: TextButton.icon(
                  onPressed: authState.isLoading
                      ? null
                      : () => _confirmLogout(context, ref),
                  style: TextButton.styleFrom(
                    backgroundColor: kPdfColor.withOpacity(0.08),
                    foregroundColor: kPdfColor,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: BorderSide(
                          color: kPdfColor.withOpacity(0.2), width: 1),
                    ),
                  ),
                  icon: authState.isLoading
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(
                              color: kPdfColor, strokeWidth: 2),
                        )
                      : const Icon(Icons.logout_rounded, size: 18),
                  label: Text(
                    'Logout',
                    style: GoogleFonts.poppins(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _navigate(BuildContext context, WidgetRef ref, int index) {
    ref.read(currentScreenProvider.notifier).state = index;
    Navigator.of(context).pop();
  }

  void _confirmLogout(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: kSurface2Color,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Logout',
          style: GoogleFonts.poppins(
            color: kTextPrimary,
            fontWeight: FontWeight.w600,
          ),
        ),
        content: Text(
          'Apakah kamu yakin ingin keluar dari akun?',
          style: GoogleFonts.poppins(color: kTextSecondary, fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              'Batal',
              style: GoogleFonts.poppins(color: kTextSecondary),
            ),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: kPdfColor,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
              elevation: 0,
            ),
            onPressed: () async {
              Navigator.pop(context); // tutup dialog
              Navigator.pop(context); // tutup drawer
              await ref.read(authNotifierProvider.notifier).signOut();
            },
            child: Text(
              'Logout',
              style: GoogleFonts.poppins(
                  color: Colors.white, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  String _buildInitials(String name) {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name.substring(0, name.length >= 2 ? 2 : 1).toUpperCase();
  }
}

// ── Nav Item Widget ───────────────────────────────────────────
class _NavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final int index;
  final int currentIndex;
  final VoidCallback onTap;
  final String? badge;

  const _NavItem({
    required this.icon,
    required this.label,
    required this.index,
    required this.currentIndex,
    required this.onTap,
    this.badge,
  });

  @override
  Widget build(BuildContext context) {
    final isActive = index == currentIndex;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
      decoration: BoxDecoration(
        color:
            isActive ? kAccentGreen.withOpacity(0.1) : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
      ),
      child: ListTile(
        onTap: onTap,
        leading: Icon(
          icon,
          color: isActive ? kAccentGreen : kTextSecondary,
          size: 20,
        ),
        title: Text(
          label,
          style: GoogleFonts.poppins(
            color: isActive ? kAccentGreen : kTextSecondary,
            fontSize: 13,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
          ),
        ),
        trailing: badge != null
            ? Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: isActive
                      ? kAccentGreen.withOpacity(0.2)
                      : kSurface2Color,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  badge!,
                  style: GoogleFonts.poppins(
                    color: isActive ? kAccentGreen : kTextTertiary,
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              )
            : null,
        dense: true,
        visualDensity: VisualDensity.compact,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
      ),
    );
  }
}
