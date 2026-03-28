// lib/screens/search_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/document_model.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';
import '../utils/helpers.dart';
import '../widgets/document_card.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen>
    with SingleTickerProviderStateMixin {
  final _controller = TextEditingController();
  final _focusNode = FocusNode();
  String _query = '';
  FileExtension? _filterExt;
  DocumentStatus? _filterStatus;
  late AnimationController _animController;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _fadeAnim = CurvedAnimation(parent: _animController, curve: Curves.easeOut);
    _animController.forward();

    // Auto-focus keyboard saat layar dibuka
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _focusNode.requestFocus();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    _animController.dispose();
    super.dispose();
  }

  List<DocumentModel> _getResults(DocumentsState state) {
    if (_query.isEmpty && _filterExt == null && _filterStatus == null) {
      return [];
    }

    return state.documents.where((doc) {
      final matchQuery = _query.isEmpty ||
          doc.fileName.toLowerCase().contains(_query.toLowerCase());
      final matchExt = _filterExt == null || doc.extension == _filterExt;
      final matchStatus =
          _filterStatus == null || doc.status == _filterStatus;
      return matchQuery && matchExt && matchStatus;
    }).toList()
      ..sort((a, b) => b.dateModified.compareTo(a.dateModified));
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(documentsProvider);
    final results = _getResults(state);
    final hasFilter = _filterExt != null || _filterStatus != null;

    return FadeTransition(
      opacity: _fadeAnim,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Search bar header ──────────────────────────────────────────────
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
                Expanded(
                  child: Container(
                    height: 44,
                    decoration: BoxDecoration(
                      color: kSurface2Color,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: _focusNode.hasFocus
                            ? kAccentGreen.withOpacity(0.5)
                            : kDividerColor,
                        width: 1.5,
                      ),
                    ),
                    child: Row(
                      children: [
                        const SizedBox(width: 12),
                        Icon(
                          Icons.search_rounded,
                          color: _query.isNotEmpty
                              ? kAccentGreen
                              : kTextTertiary,
                          size: 20,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: TextField(
                            controller: _controller,
                            focusNode: _focusNode,
                            onChanged: (v) => setState(() => _query = v),
                            style: GoogleFonts.poppins(
                              color: kTextPrimary,
                              fontSize: 14,
                            ),
                            decoration: InputDecoration(
                              hintText: 'Cari dokumen...',
                              hintStyle: GoogleFonts.poppins(
                                color: kTextTertiary,
                                fontSize: 14,
                              ),
                              border: InputBorder.none,
                              isDense: true,
                              contentPadding: EdgeInsets.zero,
                              filled: false,
                            ),
                          ),
                        ),
                        if (_query.isNotEmpty)
                          GestureDetector(
                            onTap: () => setState(() {
                              _controller.clear();
                              _query = '';
                            }),
                            child: Padding(
                              padding: const EdgeInsets.only(right: 10),
                              child: Container(
                                width: 18,
                                height: 18,
                                decoration: BoxDecoration(
                                  color: kTextTertiary.withOpacity(0.3),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.close,
                                    size: 12, color: kTextPrimary),
                              ),
                            ),
                          )
                        else
                          const SizedBox(width: 10),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // ── Filter chips ───────────────────────────────────────────────────
          Container(
            color: kSurfaceColor,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Tipe file
                SizedBox(
                  height: 38,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.fromLTRB(16, 4, 16, 4),
                    children: [
                      _FilterChip(
                        label: 'Semua Tipe',
                        isActive: _filterExt == null,
                        onTap: () => setState(() => _filterExt = null),
                      ),
                      const SizedBox(width: 8),
                      ...FileExtension.values
                          .where((e) => e != FileExtension.other)
                          .map((ext) => Padding(
                                padding: const EdgeInsets.only(right: 8),
                                child: _FilterChip(
                                  label: ext.name.toUpperCase(),
                                  color: getExtensionColor(ext),
                                  isActive: _filterExt == ext,
                                  onTap: () => setState(() =>
                                      _filterExt =
                                          _filterExt == ext ? null : ext),
                                ),
                              )),
                    ],
                  ),
                ),
                // Status
                SizedBox(
                  height: 36,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 6),
                    children: [
                      _FilterChip(
                        label: 'Semua Status',
                        isActive: _filterStatus == null,
                        onTap: () => setState(() => _filterStatus = null),
                      ),
                      const SizedBox(width: 8),
                      _FilterChip(
                        label: 'ACTIVE',
                        color: kStatusActiveColor,
                        isActive: _filterStatus == DocumentStatus.active,
                        onTap: () => setState(() => _filterStatus =
                            _filterStatus == DocumentStatus.active
                                ? null
                                : DocumentStatus.active),
                      ),
                      const SizedBox(width: 8),
                      _FilterChip(
                        label: 'ARCHIVED',
                        color: kStatusArchivedColor,
                        isActive: _filterStatus == DocumentStatus.archived,
                        onTap: () => setState(() => _filterStatus =
                            _filterStatus == DocumentStatus.archived
                                ? null
                                : DocumentStatus.archived),
                      ),
                      const SizedBox(width: 8),
                      _FilterChip(
                        label: 'DELETED',
                        color: kStatusDeletedColor,
                        isActive: _filterStatus == DocumentStatus.deleted,
                        onTap: () => setState(() => _filterStatus =
                            _filterStatus == DocumentStatus.deleted
                                ? null
                                : DocumentStatus.deleted),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          Container(height: 1, color: kDividerColor),

          // ── Body ───────────────────────────────────────────────────────────
          Expanded(
            child: _query.isEmpty && !hasFilter
                ? _EmptyPrompt(recentDocs: state.recentDocuments.take(5).toList())
                : results.isEmpty
                    ? _NoResults(query: _query)
                    : Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Padding(
                            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
                            child: Row(
                              children: [
                                Text(
                                  '${results.length} hasil ditemukan',
                                  style: GoogleFonts.poppins(
                                    color: kTextTertiary,
                                    fontSize: 12,
                                  ),
                                ),
                                if (hasFilter) ...[
                                  const SizedBox(width: 8),
                                  GestureDetector(
                                    onTap: () => setState(() {
                                      _filterExt = null;
                                      _filterStatus = null;
                                    }),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: kAccentGreen.withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(
                                            color: kAccentGreen.withOpacity(0.3)),
                                      ),
                                      child: Text(
                                        'Reset filter',
                                        style: GoogleFonts.poppins(
                                          color: kAccentGreen,
                                          fontSize: 11,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                          Expanded(
                            child: ListView.builder(
                              itemCount: results.length,
                              itemBuilder: (context, i) => DocumentCard(
                                document: results[i],
                                showDivider: i < results.length - 1,
                              ),
                            ),
                          ),
                        ],
                      ),
          ),
        ],
      ),
    );
  }
}

// ─── Filter Chip ──────────────────────────────────────────────────────────────
class _FilterChip extends StatelessWidget {
  final String label;
  final bool isActive;
  final Color? color;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.isActive,
    required this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final activeColor = color ?? kAccentGreen;
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        decoration: BoxDecoration(
          color: isActive ? activeColor.withOpacity(0.15) : kSurface2Color,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isActive ? activeColor : kDividerColor,
            width: 1,
          ),
        ),
        child: Text(
          label,
          style: GoogleFonts.poppins(
            color: isActive ? activeColor : kTextTertiary,
            fontSize: 11,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
          ),
        ),
      ),
    );
  }
}

// ─── Empty Prompt (belum ada query) ──────────────────────────────────────────
class _EmptyPrompt extends StatelessWidget {
  final List<DocumentModel> recentDocs;

  const _EmptyPrompt({required this.recentDocs});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const SizedBox(height: 12),

        // Search hint icon
        Center(
          child: Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: kSurface2Color,
              shape: BoxShape.circle,
              border: Border.all(color: kDividerColor),
            ),
            child: const Icon(Icons.manage_search_rounded,
                color: kTextTertiary, size: 34),
          ),
        ),
        const SizedBox(height: 12),
        Center(
          child: Text(
            'Ketik untuk mencari dokumen',
            style: GoogleFonts.poppins(
              color: kTextTertiary,
              fontSize: 13,
            ),
          ),
        ),
        const SizedBox(height: 24),

        // Recent section
        if (recentDocs.isNotEmpty) ...[
          Text(
            'Terbaru',
            style: GoogleFonts.poppins(
              color: kTextSecondary,
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 10),
          ...recentDocs.map((doc) => _RecentSearchItem(document: doc)),
        ],
      ],
    );
  }
}

class _RecentSearchItem extends StatelessWidget {
  final DocumentModel document;

  const _RecentSearchItem({required this.document});

  @override
  Widget build(BuildContext context) {
    final color = getExtensionColor(document.extension);
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: kSurface2Color,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: kDividerColor),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(_icon(document.extension), color: color, size: 18),
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
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    '${document.extensionString} · ${document.formattedSize} · ${DateFormat('dd MMM yyyy').format(document.dateModified)}',
                    style:
                        GoogleFonts.poppins(color: kTextTertiary, fontSize: 11),
                  ),
                ],
              ),
            ),
            const Icon(Icons.north_west_rounded, color: kTextTertiary, size: 16),
          ],
        ),
      ),
    );
  }

  IconData _icon(FileExtension ext) {
    switch (ext) {
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
      default:
        return Icons.insert_drive_file_outlined;
    }
  }
}

// ─── No Results ───────────────────────────────────────────────────────────────
class _NoResults extends StatelessWidget {
  final String query;

  const _NoResults({required this.query});

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
              border: Border.all(color: kDividerColor),
            ),
            child: const Icon(Icons.search_off_rounded,
                color: kTextTertiary, size: 34),
          ),
          const SizedBox(height: 14),
          Text(
            'Tidak ada hasil untuk',
            style: GoogleFonts.poppins(color: kTextTertiary, fontSize: 13),
          ),
          const SizedBox(height: 4),
          Text(
            '"$query"',
            style: GoogleFonts.poppins(
              color: kTextPrimary,
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Coba kata kunci lain atau ubah filter',
            style: GoogleFonts.poppins(color: kTextTertiary, fontSize: 12),
          ),
        ],
      ),
    );
  }
}
