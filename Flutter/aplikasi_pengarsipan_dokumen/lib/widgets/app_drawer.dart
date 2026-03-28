// lib/widgets/app_drawer.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
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

    return Drawer(
      backgroundColor: kSurfaceColor,
      child: SafeArea(
        child: Column(
          children: [
            // Logo header
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

            // Platform label
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

            // Nav items
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
              badge: state.archivedDocuments.length > 0
                  ? '${state.archivedDocuments.length}'
                  : null,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 5),
            ),
            _NavItem(
              icon: Icons.delete_outline_rounded,
              label: 'Trash',
              index: 6,
              badge: state.trashedDocuments.length > 0
                  ? '${state.trashedDocuments.length}'
                  : null,
              currentIndex: currentIndex,
              onTap: () => _navigate(context, ref, 6),
            ),

            const Spacer(),
            const Divider(color: kDividerColor, height: 1),

            // User profile
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: const BoxDecoration(
                      color: kOwnerAvatarColor,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        'DP',
                        style: GoogleFonts.poppins(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Dzaki Prasetyo',
                          style: GoogleFonts.poppins(
                            color: kTextPrimary,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          'dzakiprasetyo98@gmail.com',
                          style: GoogleFonts.poppins(
                            color: kTextTertiary,
                            fontSize: 10,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.keyboard_arrow_up_rounded,
                      color: kTextTertiary, size: 18),
                ],
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
}

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
        color: isActive ? kAccentGreen.withOpacity(0.1) : Colors.transparent,
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
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
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
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
      ),
    );
  }
}
