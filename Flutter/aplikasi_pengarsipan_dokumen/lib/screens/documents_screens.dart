// lib/screens/documents_screens.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';
import '../widgets/document_card.dart';
import '../widgets/search_sort_bar.dart';

// ─── Base Document List Screen ─────────────────────────────────────────────────
class _BaseDocumentScreen extends ConsumerWidget {
  final String title;
  final List Function(DocumentsState) getDocuments;
  final String emptyMessage;
  final IconData emptyIcon;

  const _BaseDocumentScreen({
    required this.title,
    required this.getDocuments,
    required this.emptyMessage,
    required this.emptyIcon,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(documentsProvider);
    final documents = getDocuments(state);

    return Column(
      children: [
        // Header bar
        Container(
          color: kSurfaceColor,
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
          child: Row(
            children: [
              Builder(
                builder: (ctx) => IconButton(
                  icon: const Icon(Icons.menu_rounded, color: kTextPrimary),
                  onPressed: () => Scaffold.of(ctx).openDrawer(),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
              ),
              const SizedBox(width: 12),
              Text(
                title,
                style: GoogleFonts.poppins(
                  color: kTextPrimary,
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: kSurface2Color,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${documents.length} items',
                  style: GoogleFonts.poppins(
                    color: kTextTertiary,
                    fontSize: 11,
                  ),
                ),
              ),
            ],
          ),
        ),
        const Divider(height: 1, color: kDividerColor),

        // Search & Sort
        const SearchSortBar(),

        // Document list
        Expanded(
          child: documents.isEmpty
              ? _EmptyState(message: emptyMessage, icon: emptyIcon)
              : ListView.builder(
                  itemCount: documents.length,
                  itemBuilder: (context, index) {
                    return DocumentCard(
                      document: documents[index],
                      showDivider: index < documents.length - 1,
                    );
                  },
                ),
        ),
      ],
    );
  }
}

// ─── My Documents Screen ──────────────────────────────────────────────────────
class MyDocumentsScreen extends ConsumerWidget {
  const MyDocumentsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _BaseDocumentScreen(
      title: 'My Documents',
      getDocuments: (state) => state.myDocuments,
      emptyMessage: 'No documents yet.\nUpload your first file!',
      emptyIcon: Icons.description_outlined,
    );
  }
}

// ─── Starred Screen ───────────────────────────────────────────────────────────
class StarredScreen extends ConsumerWidget {
  const StarredScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _BaseDocumentScreen(
      title: 'Starred',
      getDocuments: (state) => state.starredDocuments,
      emptyMessage: 'No starred documents.\nStar files to find them quickly.',
      emptyIcon: Icons.star_outline_rounded,
    );
  }
}

// ─── Archives Screen ──────────────────────────────────────────────────────────
class ArchivesScreen extends ConsumerWidget {
  const ArchivesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _BaseDocumentScreen(
      title: 'Archives',
      getDocuments: (state) => state.archivedDocuments,
      emptyMessage: 'No archived documents.',
      emptyIcon: Icons.inventory_2_outlined,
    );
  }
}

// ─── Trash Screen ─────────────────────────────────────────────────────────────
class TrashScreen extends ConsumerWidget {
  const TrashScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(documentsProvider.notifier);

    return Column(
      children: [
        _TrashHeader(onEmptyTrash: () => _confirmEmptyTrash(context, notifier)),
        Expanded(
          child: _BaseDocumentScreen(
            title: 'Trash',
            getDocuments: (state) => state.trashedDocuments,
            emptyMessage: 'Trash is empty.',
            emptyIcon: Icons.delete_outline_rounded,
          ),
        ),
      ],
    );
  }

  void _confirmEmptyTrash(BuildContext context, DocumentsNotifier notifier) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: kSurface2Color,
        title: Text(
          'Empty Trash',
          style: GoogleFonts.poppins(
              color: kTextPrimary, fontWeight: FontWeight.w600),
        ),
        content: Text(
          'Permanently delete all items in trash? This cannot be undone.',
          style: GoogleFonts.poppins(color: kTextSecondary, fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel',
                style: GoogleFonts.poppins(color: kTextSecondary)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: kPdfColor),
            onPressed: () {
              final state = notifier.state;
              for (final doc in state.trashedDocuments) {
                notifier.deletePermanently(doc.id);
              }
              Navigator.pop(context);
            },
            child: Text('Empty Trash',
                style: GoogleFonts.poppins(color: Colors.white)),
          ),
        ],
      ),
    );
  }
}

class _TrashHeader extends StatelessWidget {
  final VoidCallback onEmptyTrash;

  const _TrashHeader({required this.onEmptyTrash});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      color: kPdfColor.withOpacity(0.08),
      child: Row(
        children: [
          const Icon(Icons.info_outline_rounded, color: kPdfColor, size: 16),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'Items in trash are permanently deleted after 30 days',
              style: GoogleFonts.poppins(color: kPdfColor, fontSize: 11),
            ),
          ),
          TextButton(
            onPressed: onEmptyTrash,
            child: Text(
              'Empty Trash',
              style:
                  GoogleFonts.poppins(color: kPdfColor, fontSize: 11, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Recently Screen ──────────────────────────────────────────────────────────
class RecentlyScreen extends ConsumerWidget {
  const RecentlyScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _BaseDocumentScreen(
      title: 'Recently',
      getDocuments: (state) => state.recentDocuments,
      emptyMessage: 'No recent documents.',
      emptyIcon: Icons.access_time_rounded,
    );
  }
}

// ─── Empty State ──────────────────────────────────────────────────────────────
class _EmptyState extends StatelessWidget {
  final String message;
  final IconData icon;

  const _EmptyState({required this.message, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: kSurface2Color,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: kTextTertiary, size: 34),
          ),
          const SizedBox(height: 16),
          Text(
            message,
            textAlign: TextAlign.center,
            style: GoogleFonts.poppins(
              color: kTextTertiary,
              fontSize: 14,
              height: 1.6,
            ),
          ),
        ],
      ),
    );
  }
}
