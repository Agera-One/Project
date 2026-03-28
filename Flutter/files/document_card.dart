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
        extentRatio: 0.55,
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
              onPressed: (_) {
                notifier.deletePermanently(document.id);
              },
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
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // File icon + extension badge
                    _FileIconWidget(extension: document.extension),
                    const SizedBox(width: 12),

                    // File name + owner info
                    Expanded(
                      flex: 3,
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
                                const SizedBox(width: 4),
                                const Icon(Icons.star_rounded,
                                    color: Color(0xFFFFB300), size: 14),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              _OwnerAvatar(initials: document.ownerInitials),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      document.ownerName,
                                      style: GoogleFonts.poppins(
                                        color: kTextSecondary,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                    Text(
                                      document.ownerEmail,
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
                        ],
                      ),
                    ),

                    const SizedBox(width: 8),

                    // Size + Date + Status
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          document.formattedSize,
                          style: GoogleFonts.poppins(
                            color: kTextSecondary,
                            fontSize: 11,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          DateFormat('dd MMM yyyy').format(document.dateModified),
                          style: GoogleFonts.poppins(
                            color: kTextTertiary,
                            fontSize: 10,
                          ),
                        ),
                        const SizedBox(height: 4),
                        _StatusBadge(status: document.status),
                      ],
                    ),

                    const SizedBox(width: 4),

                    // Three-dot menu
                    _ThreeDotMenu(document: document),
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
}

// ─── File Icon Widget ─────────────────────────────────────────────────────────
class _FileIconWidget extends StatelessWidget {
  final FileExtension extension;

  const _FileIconWidget({required this.extension});

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        Container(
          width: 40,
          height: 44,
          decoration: BoxDecoration(
            color: kSurface2Color,
            borderRadius: BorderRadius.circular(6),
          ),
          child: Icon(
            _getIcon(),
            color: kTextSecondary,
            size: 22,
          ),
        ),
        Positioned(
          bottom: -4,
          right: -6,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
            decoration: BoxDecoration(
              color: getExtensionColor(extension),
              borderRadius: BorderRadius.circular(3),
            ),
            child: Text(
              _getLabel(),
              style: GoogleFonts.poppins(
                color: Colors.white,
                fontSize: 7,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.3,
              ),
            ),
          ),
        ),
      ],
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

  String _getLabel() {
    switch (extension) {
      case FileExtension.png:
        return 'PNG';
      case FileExtension.jpg:
        return 'JPG';
      case FileExtension.pdf:
        return 'PDF';
      case FileExtension.docx:
        return 'DOCX';
      case FileExtension.pptx:
        return 'PPTX';
      case FileExtension.xlsx:
        return 'XLSX';
      case FileExtension.txt:
        return 'TXT';
      default:
        return 'FILE';
    }
  }
}

// ─── Owner Avatar ─────────────────────────────────────────────────────────────
class _OwnerAvatar extends StatelessWidget {
  final String initials;

  const _OwnerAvatar({required this.initials});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 22,
      height: 22,
      decoration: const BoxDecoration(
        color: kOwnerAvatarColor,
        shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(
          initials,
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontSize: 8,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
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
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bgColor.withOpacity(0.15),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: bgColor.withOpacity(0.6), width: 1),
      ),
      child: Text(
        label,
        style: GoogleFonts.poppins(
          color: bgColor,
          fontSize: 9,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.4,
        ),
      ),
    );
  }
}

// ─── Extension Badge (standalone) ─────────────────────────────────────────────
class ExtensionBadge extends StatelessWidget {
  final FileExtension extension;

  const ExtensionBadge({super.key, required this.extension});

  @override
  Widget build(BuildContext context) {
    final color = getExtensionColor(extension);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(
        _label(),
        style: GoogleFonts.poppins(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  String _label() {
    switch (extension) {
      case FileExtension.png: return 'PNG';
      case FileExtension.jpg: return 'JPG';
      case FileExtension.pdf: return 'PDF';
      case FileExtension.docx: return 'DOCX';
      case FileExtension.pptx: return 'PPTX';
      case FileExtension.xlsx: return 'XLSX';
      case FileExtension.txt: return 'TXT';
      default: return 'FILE';
    }
  }
}

// ─── Three Dot Menu ───────────────────────────────────────────────────────────
class _ThreeDotMenu extends ConsumerWidget {
  final DocumentModel document;

  const _ThreeDotMenu({required this.document});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(documentsProvider.notifier);

    return PopupMenuButton<String>(
      color: kSurface2Color,
      icon: const Icon(Icons.more_vert, color: kTextTertiary, size: 18),
      padding: EdgeInsets.zero,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      onSelected: (value) {
        switch (value) {
          case 'star':
            notifier.toggleStar(document.id);
          case 'archive':
            notifier.archiveDocument(document.id);
          case 'restore':
            notifier.restoreDocument(document.id);
          case 'trash':
            notifier.moveToTrash(document.id);
          case 'delete':
            _confirmDelete(context, notifier);
        }
      },
      itemBuilder: (context) => [
        if (document.status == DocumentStatus.active || document.status == DocumentStatus.archived)
          PopupMenuItem(
            value: 'star',
            child: Row(
              children: [
                Icon(
                  document.isStarred ? Icons.star_rounded : Icons.star_border_rounded,
                  color: const Color(0xFFFFB300),
                  size: 18,
                ),
                const SizedBox(width: 8),
                Text(
                  document.isStarred ? 'Unstar' : 'Star',
                  style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 13),
                ),
              ],
            ),
          ),
        if (document.status == DocumentStatus.active)
          PopupMenuItem(
            value: 'archive',
            child: Row(
              children: [
                const Icon(Icons.archive_rounded, color: kStatusArchivedColor, size: 18),
                const SizedBox(width: 8),
                Text(
                  'Archive',
                  style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 13),
                ),
              ],
            ),
          ),
        if (document.status == DocumentStatus.archived || document.status == DocumentStatus.deleted)
          PopupMenuItem(
            value: 'restore',
            child: Row(
              children: [
                const Icon(Icons.restore_rounded, color: kStatusActiveColor, size: 18),
                const SizedBox(width: 8),
                Text(
                  'Restore',
                  style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 13),
                ),
              ],
            ),
          ),
        if (document.status != DocumentStatus.deleted)
          PopupMenuItem(
            value: 'trash',
            child: Row(
              children: [
                const Icon(Icons.delete_outline_rounded, color: kPdfColor, size: 18),
                const SizedBox(width: 8),
                Text(
                  'Move to Trash',
                  style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 13),
                ),
              ],
            ),
          ),
        if (document.status == DocumentStatus.deleted)
          PopupMenuItem(
            value: 'delete',
            child: Row(
              children: [
                const Icon(Icons.delete_forever_rounded, color: kPdfColor, size: 18),
                const SizedBox(width: 8),
                Text(
                  'Delete Permanently',
                  style: GoogleFonts.poppins(color: kPdfColor, fontSize: 13),
                ),
              ],
            ),
          ),
      ],
    );
  }

  void _confirmDelete(BuildContext context, DocumentsNotifier notifier) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: kSurface2Color,
        title: Text(
          'Delete Permanently',
          style: GoogleFonts.poppins(color: kTextPrimary, fontWeight: FontWeight.w600),
        ),
        content: Text(
          'This action cannot be undone. Are you sure you want to permanently delete "${document.fileName}"?',
          style: GoogleFonts.poppins(color: kTextSecondary, fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Cancel', style: GoogleFonts.poppins(color: kTextSecondary)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: kPdfColor),
            onPressed: () {
              notifier.deletePermanently(document.id);
              Navigator.pop(context);
            },
            child: Text('Delete', style: GoogleFonts.poppins(color: Colors.white)),
          ),
        ],
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
      initialChildSize: 0.55,
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
            // Handle
            Center(
              child: Container(
                margin: const EdgeInsets.only(top: 12, bottom: 16),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: kDividerColor,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),

            // File preview placeholder
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              height: 140,
              decoration: BoxDecoration(
                color: kCardColor,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      _getFileIcon(document.extension),
                      size: 48,
                      color: getExtensionColor(document.extension),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      document.extensionString,
                      style: GoogleFonts.poppins(
                        color: getExtensionColor(document.extension),
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 16),

            // File name
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      document.fileName,
                      style: GoogleFonts.poppins(
                        color: kTextPrimary,
                        fontSize: 17,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: () => notifier.toggleStar(document.id),
                    icon: Icon(
                      document.isStarred ? Icons.star_rounded : Icons.star_border_rounded,
                      color: const Color(0xFFFFB300),
                    ),
                  ),
                ],
              ),
            ),

            // Details
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                children: [
                  _DetailRow(label: 'Owner', value: '${document.ownerName} · ${document.ownerEmail}'),
                  _DetailRow(label: 'Size', value: document.formattedSize),
                  _DetailRow(
                    label: 'Modified',
                    value: DateFormat('dd MMMM yyyy').format(document.dateModified),
                  ),
                  _DetailRow(label: 'Extension', value: document.extensionString),
                  _DetailRow(
                    label: 'Status',
                    value: document.status.name.toUpperCase(),
                    valueColor: _statusColor(document.status),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),
            const Divider(color: kDividerColor),
            const SizedBox(height: 8),

            // Actions
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
                      label: document.status == DocumentStatus.deleted ? 'Delete' : 'Trash',
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
            width: 80,
            child: Text(
              label,
              style: GoogleFonts.poppins(color: kTextTertiary, fontSize: 12),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: GoogleFonts.poppins(
                color: valueColor ?? kTextSecondary,
                fontSize: 12,
                fontWeight: FontWeight.w500,
              ),
            ),
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

  const _ActionButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

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
            Text(
              label,
              style: GoogleFonts.poppins(color: color, fontSize: 11, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }
}
