// lib/widgets/document_card.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/document_model.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';
import '../utils/helpers.dart';

class DocumentCard extends ConsumerWidget {
  final DocumentModel document;
  final bool showDivider;

  const DocumentCard({
    super.key,
    required this.document,
    this.showDivider = true,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(documentsProvider.notifier);

    return Slidable(
      key: ValueKey(document.id),
      endActionPane: ActionPane(
        motion: const DrawerMotion(),
        extentRatio: 0.5,
        children: [
          if (document.status == DocumentStatus.active)
            SlidableAction(
              onPressed: (_) => notifier.archiveDocument(document.id),
              backgroundColor: kStatusArchivedColor,
              foregroundColor: Colors.black,
              icon: Icons.archive_rounded,
              label: 'Archive',
              borderRadius: BorderRadius.zero,
            ),
          if (document.status == DocumentStatus.deleted)
            SlidableAction(
              onPressed: (_) => notifier.restoreDocument(document.id),
              backgroundColor: kStatusActiveColor,
              foregroundColor: Colors.black,
              icon: Icons.restore_rounded,
              label: 'Restore',
              borderRadius: BorderRadius.zero,
            ),
          if (document.status == DocumentStatus.deleted)
            SlidableAction(
              onPressed: (_) => notifier.deletePermanently(document.id),
              backgroundColor: kPdfColor,
              foregroundColor: kTextPrimary,
              icon: Icons.delete_forever_rounded,
              label: 'Delete',
              borderRadius: BorderRadius.zero,
            )
          else
            SlidableAction(
              onPressed: (_) => notifier.moveToTrash(document.id),
              backgroundColor: kPdfColor,
              foregroundColor: kTextPrimary,
              icon: Icons.delete_outline_rounded,
              label: 'Trash',
              borderRadius: BorderRadius.zero,
            ),
        ],
      ),
      child: InkWell(
        onTap: () => _showDocumentDetail(context, ref),
        child: Container(
          color: kBgColor,
          child: Column(
            children: [
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    _FileIconWidget(extension: document.extension),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Flexible(
                                child: Text(
                                  document.fileName,
                                  style: GoogleFonts.poppins(
                                    color: kTextPrimary,
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w500,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (document.isStarred) ...[
                                const SizedBox(width: 5),
                                const Icon(Icons.star_rounded,
                                    color: Color(0xFFFFB300), size: 14),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${document.extensionString} · ${document.formattedSize} · ${DateFormat('dd MMM yyyy').format(document.dateModified)}',
                            style: GoogleFonts.poppins(
                              color: kTextTertiary,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    _StatusBadge(status: document.status),
                    const SizedBox(width: 2),
                    IconButton(
                      icon: const Icon(Icons.more_vert,
                          color: kTextTertiary, size: 20),
                      padding: EdgeInsets.zero,
                      constraints:
                          const BoxConstraints(minWidth: 32, minHeight: 32),
                      onPressed: () => _showActionMenu(context, ref),
                    ),
                  ],
                ),
              ),
              if (showDivider)
                const Divider(height: 1, thickness: 1, color: kDividerColor),
            ],
          ),
        ),
      ),
    );
  }

  void _showDocumentDetail(BuildContext context, WidgetRef ref) {
    showModalBottomSheet(
      context: context,
      backgroundColor: kSurface2Color,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => _DocumentDetailSheet(document: document, ref: ref),
    );
  }

  void _showActionMenu(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(documentsProvider.notifier);

    showModalBottomSheet(
      context: context,
      backgroundColor: kSurface2Color,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Handle
                Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: kDividerColor,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),

                // File header
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 4, 20, 12),
                  child: Row(
                    children: [
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: getExtensionColor(document.extension)
                              .withOpacity(0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          _fileIconData(document.extension),
                          color: getExtensionColor(document.extension),
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              document.fileName,
                              style: GoogleFonts.poppins(
                                color: kTextPrimary,
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              '${document.extensionString} · ${document.formattedSize}',
                              style: GoogleFonts.poppins(
                                  color: kTextTertiary, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const Divider(color: kDividerColor, height: 1),
                const SizedBox(height: 4),

                // Download
                _MenuAction(
                  icon: Icons.download_rounded,
                  label: 'Download',
                  iconColor: kTextPrimary,
                  onTap: () {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                      content: Text('Download belum tersedia',
                          style: GoogleFonts.poppins(
                            fontSize: 13,
                            color: kTextPrimary)),
                      backgroundColor: kSurface2Color,
                    ));
                  },
                ),

                // Star / Unstar
                if (document.status != DocumentStatus.deleted)
                  _MenuAction(
                    icon: document.isStarred
                        ? Icons.star_rounded
                        : Icons.star_border_rounded,
                    label: document.isStarred ? 'Unstar' : 'Star',
                    iconColor: const Color(0xFFFFB300),
                    onTap: () {
                      notifier.toggleStar(document.id);
                      Navigator.pop(context);
                    },
                  ),

                // Archive
                if (document.status == DocumentStatus.active)
                  _MenuAction(
                    icon: Icons.inventory_2_outlined,
                    label: 'Archive',
                    iconColor: kStatusArchivedColor,
                    onTap: () {
                      notifier.archiveDocument(document.id);
                      Navigator.pop(context);
                    },
                  ),

                // Restore
                if (document.status == DocumentStatus.archived ||
                    document.status == DocumentStatus.deleted)
                  _MenuAction(
                    icon: Icons.restore_rounded,
                    label: 'Restore',
                    iconColor: kStatusActiveColor,
                    onTap: () {
                      notifier.restoreDocument(document.id);
                      Navigator.pop(context);
                    },
                  ),

                // Delete permanently
                if (document.status == DocumentStatus.deleted)
                  _MenuAction(
                    icon: Icons.delete_forever_rounded,
                    label: 'Delete Permanently',
                    iconColor: kPdfColor,
                    isDestructive: true,
                    onTap: () {
                      Navigator.pop(context);
                      _confirmDelete(context, notifier);
                    },
                  )
                else
                  _MenuAction(
                    icon: Icons.delete_outline_rounded,
                    label: 'Delete',
                    iconColor: kPdfColor,
                    isDestructive: true,
                    onTap: () {
                      notifier.moveToTrash(document.id);
                      Navigator.pop(context);
                    },
                  ),

                const SizedBox(height: 8),
              ],
            ),
          ),
        );
      },
    );
  }

  void _confirmDelete(BuildContext context, DocumentsNotifier notifier) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: kSurface2Color,
        title: Text('Delete Permanently',
            style: GoogleFonts.poppins(
                color: kTextPrimary, fontWeight: FontWeight.w600)),
        content: Text(
          'Aksi ini tidak bisa dibatalkan. Hapus "${document.fileName}" selamanya?',
          style: GoogleFonts.poppins(color: kTextSecondary, fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Batal',
                style: GoogleFonts.poppins(color: kTextSecondary)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: kPdfColor),
            onPressed: () {
              notifier.deletePermanently(document.id);
              Navigator.pop(context);
            },
            child:
                Text('Hapus', style: GoogleFonts.poppins(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  IconData _fileIconData(FileExtension ext) {
    switch (ext) {
      case FileExtension.png:
      case FileExtension.jpg:
        return Icons.image_rounded;
      case FileExtension.pdf:
        return Icons.picture_as_pdf_rounded;
      case FileExtension.docx:
        return Icons.description_rounded;
      case FileExtension.pptx:
        return Icons.slideshow_rounded;
      case FileExtension.xlsx:
        return Icons.table_chart_rounded;
      default:
        return Icons.insert_drive_file_rounded;
    }
  }
}

// ─── Menu Action Item ─────────────────────────────────────────────────────────
class _MenuAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color iconColor;
  final VoidCallback onTap;
  final bool isDestructive;

  const _MenuAction({
    required this.icon,
    required this.label,
    required this.iconColor,
    required this.onTap,
    this.isDestructive = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Row(
          children: [
            Icon(icon, color: iconColor, size: 24),
            const SizedBox(width: 16),
            Text(
              label,
              style: GoogleFonts.poppins(
                color: isDestructive ? kPdfColor : kTextPrimary,
                fontSize: 15,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── File Icon Widget ─────────────────────────────────────────────────────────
class _FileIconWidget extends StatelessWidget {
  final FileExtension extension;

  const _FileIconWidget({required this.extension});

  @override
  Widget build(BuildContext context) {
    final color = getExtensionColor(extension);
    return Container(
      width: 46,
      height: 46,
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Icon(_getIcon(), color: color, size: 24),
    );
  }

  IconData _getIcon() {
    switch (extension) {
      case FileExtension.png:
      case FileExtension.jpg:
        return Icons.image_outlined;
      case FileExtension.pdf:
        return Icons.picture_as_pdf_outlined;
      case FileExtension.docx:
        return Icons.description_outlined;
      case FileExtension.pptx:
        return Icons.slideshow_outlined;
      case FileExtension.xlsx:
        return Icons.table_chart_outlined;
      case FileExtension.txt:
        return Icons.text_snippet_outlined;
      default:
        return Icons.insert_drive_file_outlined;
    }
  }
}

// ─── Status Badge ─────────────────────────────────────────────────────────────
class _StatusBadge extends StatelessWidget {
  final DocumentStatus status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color bgColor;
    String label;

    switch (status) {
      case DocumentStatus.active:
        bgColor = kStatusActiveColor;
        label = 'ACTIVE';
      case DocumentStatus.archived:
        bgColor = kStatusArchivedColor;
        label = 'ARCHIVED';
      case DocumentStatus.deleted:
        bgColor = kStatusDeletedColor;
        label = 'DELETED';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: bgColor.withOpacity(0.12),
        borderRadius: BorderRadius.circular(5),
        border: Border.all(color: bgColor.withOpacity(0.5), width: 1),
      ),
      child: Text(
        label,
        style: GoogleFonts.poppins(
          color: bgColor,
          fontSize: 9,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

// ─── Extension Badge ──────────────────────────────────────────────────────────
class ExtensionBadge extends StatelessWidget {
  final FileExtension extension;

  const ExtensionBadge({super.key, required this.extension});

  @override
  Widget build(BuildContext context) {
    final color = getExtensionColor(extension);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration:
          BoxDecoration(color: color, borderRadius: BorderRadius.circular(5)),
      child: Text(
        extension.name.toUpperCase(),
        style: GoogleFonts.poppins(
            color: Colors.white, fontSize: 10, fontWeight: FontWeight.w700),
      ),
    );
  }
}

// ─── Document Detail Bottom Sheet ─────────────────────────────────────────────
class _DocumentDetailSheet extends ConsumerWidget {
  final DocumentModel document;
  final WidgetRef ref;

  const _DocumentDetailSheet({required this.document, required this.ref});

  @override
  Widget build(BuildContext context, WidgetRef widgetRef) {
    final notifier = widgetRef.read(documentsProvider.notifier);

    return DraggableScrollableSheet(
      initialChildSize: 0.52,
      maxChildSize: 0.85,
      minChildSize: 0.4,
      expand: false,
      builder: (context, controller) => Container(
        decoration: const BoxDecoration(
          color: kSurface2Color,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: ListView(
          controller: controller,
          children: [
            Center(
              child: Container(
                margin: const EdgeInsets.only(top: 12, bottom: 16),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                    color: kDividerColor,
                    borderRadius: BorderRadius.circular(2)),
              ),
            ),
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              height: 130,
              decoration: BoxDecoration(
                  color: kCardColor, borderRadius: BorderRadius.circular(12)),
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(_getFileIcon(document.extension),
                        size: 48, color: getExtensionColor(document.extension)),
                    const SizedBox(height: 6),
                    Text(document.extensionString,
                        style: GoogleFonts.poppins(
                            color: getExtensionColor(document.extension),
                            fontWeight: FontWeight.w700,
                            fontSize: 12)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Expanded(
                    child: Text(document.fileName,
                        style: GoogleFonts.poppins(
                            color: kTextPrimary,
                            fontSize: 17,
                            fontWeight: FontWeight.w600)),
                  ),
                  IconButton(
                    onPressed: () => notifier.toggleStar(document.id),
                    icon: Icon(
                        document.isStarred
                            ? Icons.star_rounded
                            : Icons.star_border_rounded,
                        color: const Color(0xFFFFB300)),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                children: [
                  _DetailRow(label: 'Ukuran', value: document.formattedSize),
                  _DetailRow(
                      label: 'Diubah',
                      value: DateFormat('dd MMMM yyyy')
                          .format(document.dateModified)),
                  _DetailRow(label: 'Tipe', value: document.extensionString),
                  _DetailRow(
                      label: 'Status',
                      value: document.status.name.toUpperCase(),
                      valueColor: _statusColor(document.status)),
                ],
              ),
            ),
            const SizedBox(height: 16),
            const Divider(color: kDividerColor),
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  if (document.status != DocumentStatus.deleted) ...[
                    Expanded(
                      child: _ActionButton(
                        icon: Icons.archive_rounded,
                        label: 'Archive',
                        color: kStatusArchivedColor,
                        onTap: () {
                          notifier.archiveDocument(document.id);
                          Navigator.pop(context);
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],
                  if (document.status == DocumentStatus.deleted) ...[
                    Expanded(
                      child: _ActionButton(
                        icon: Icons.restore_rounded,
                        label: 'Restore',
                        color: kStatusActiveColor,
                        onTap: () {
                          notifier.restoreDocument(document.id);
                          Navigator.pop(context);
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],
                  Expanded(
                    child: _ActionButton(
                      icon: document.status == DocumentStatus.deleted
                          ? Icons.delete_forever_rounded
                          : Icons.delete_outline_rounded,
                      label: document.status == DocumentStatus.deleted
                          ? 'Delete'
                          : 'Trash',
                      color: kPdfColor,
                      onTap: () {
                        if (document.status == DocumentStatus.deleted) {
                          notifier.deletePermanently(document.id);
                        } else {
                          notifier.moveToTrash(document.id);
                        }
                        Navigator.pop(context);
                      },
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  IconData _getFileIcon(FileExtension ext) {
    switch (ext) {
      case FileExtension.png:
      case FileExtension.jpg:
        return Icons.image_rounded;
      case FileExtension.pdf:
        return Icons.picture_as_pdf_rounded;
      case FileExtension.docx:
        return Icons.description_rounded;
      case FileExtension.pptx:
        return Icons.slideshow_rounded;
      case FileExtension.xlsx:
        return Icons.table_chart_rounded;
      default:
        return Icons.insert_drive_file_rounded;
    }
  }

  Color _statusColor(DocumentStatus s) {
    switch (s) {
      case DocumentStatus.active:
        return kStatusActiveColor;
      case DocumentStatus.archived:
        return kStatusArchivedColor;
      case DocumentStatus.deleted:
        return kStatusDeletedColor;
    }
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;

  const _DetailRow({required this.label, required this.value, this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 70,
            child: Text(label,
                style: GoogleFonts.poppins(color: kTextTertiary, fontSize: 12)),
          ),
          Expanded(
            child: Text(value,
                style: GoogleFonts.poppins(
                    color: valueColor ?? kTextSecondary,
                    fontSize: 12,
                    fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionButton(
      {required this.icon,
      required this.label,
      required this.color,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 4),
            Text(label,
                style: GoogleFonts.poppins(
                    color: color, fontSize: 11, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}
