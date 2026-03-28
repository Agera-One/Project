// lib/screens/dashboard_screen.dart
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/document_model.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';
import '../utils/helpers.dart';
import '../widgets/storage_progress.dart';
import '../widgets/app_drawer.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(documentsProvider);

    return Scaffold(
      backgroundColor: kBgColor,
      body: CustomScrollView(
        slivers: [
          // App bar with greeting
          SliverToBoxAdapter(
            child: _DashboardHeader(
              usedGb: state.usedStorageGb,
              maxGb: state.maxStorageGb,
            ),
          ),

          // Stat cards
          SliverToBoxAdapter(
            child: _StatCards(
              total: state.totalDocumentCount,
              starred: state.starredCount,
              newThisWeek: state.newThisWeek,
              usedMb: state.totalStorageMb,
            ),
          ),

          // Quick Actions
          SliverToBoxAdapter(
            child: _QuickActions(ref: ref),
          ),

          // Recent Activity
          SliverToBoxAdapter(
            child: _RecentSection(documents: state.recentDocuments),
          ),

          // Starred Quick View
          SliverToBoxAdapter(
            child: _StarredSection(documents: state.starredDocuments),
          ),

          // Storage Breakdown
          SliverToBoxAdapter(
            child: _StorageBreakdownSection(breakdown: state.storageByType),
          ),

          const SliverToBoxAdapter(child: SizedBox(height: 100)),
        ],
      ),
    );
  }
}

// ─── Header ───────────────────────────────────────────────────────────────────
class _DashboardHeader extends StatelessWidget {
  final double usedGb;
  final double maxGb;

  const _DashboardHeader({required this.usedGb, required this.maxGb});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Halo, Dzaki! 👋',
                      style: GoogleFonts.poppins(
                        color: kTextPrimary,
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    Text(
                      DateFormat('EEEE, d MMMM yyyy').format(DateTime.now()),
                      style: GoogleFonts.poppins(
                        color: kTextTertiary,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                width: 40,
                height: 40,
                decoration: const BoxDecoration(
                  color: kOwnerAvatarColor,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    'DP',
                    style: GoogleFonts.poppins(
                      color: Colors.white,
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          StorageProgressWidget(usedGb: usedGb, maxGb: maxGb),
        ],
      ),
    );
  }
}

// ─── Stat Cards ───────────────────────────────────────────────────────────────
class _StatCards extends StatelessWidget {
  final int total;
  final int starred;
  final int newThisWeek;
  final double usedMb;

  const _StatCards({
    required this.total,
    required this.starred,
    required this.newThisWeek,
    required this.usedMb,
  });

  @override
  Widget build(BuildContext context) {
    String usedLabel = usedMb < 1024
        ? '${usedMb.toStringAsFixed(0)} MB'
        : '${(usedMb / 1024).toStringAsFixed(2)} GB';

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            child: _StatCard(
              icon: Icons.folder_rounded,
              iconColor: kDocxColor,
              value: '$total',
              label: 'Total Docs',
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _StatCard(
              icon: Icons.star_rounded,
              iconColor: const Color(0xFFFFB300),
              value: '$starred',
              label: 'Starred',
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _StatCard(
              icon: Icons.fiber_new_rounded,
              iconColor: kAccentGreenDark,
              value: '$newThisWeek',
              label: 'New/Week',
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _StatCard(
              icon: Icons.storage_rounded,
              iconColor: kPptxColor,
              value: usedLabel,
              label: 'Used',
              smallValue: true,
            ),
          ),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String value;
  final String label;
  final bool smallValue;

  const _StatCard({
    required this.icon,
    required this.iconColor,
    required this.value,
    required this.label,
    this.smallValue = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: kSurface2Color,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: kDividerColor),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: iconColor, size: 16),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: GoogleFonts.poppins(
              color: kTextPrimary,
              fontSize: smallValue ? 12 : 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: GoogleFonts.poppins(
              color: kTextTertiary,
              fontSize: 9.5,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

// ─── Quick Actions ─────────────────────────────────────────────────────────────
class _QuickActions extends StatelessWidget {
  final WidgetRef ref;

  const _QuickActions({required this.ref});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Quick Actions',
            style: GoogleFonts.poppins(
              color: kTextPrimary,
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _QuickActionCard(
                  icon: Icons.cloud_upload_outlined,
                  label: 'Upload\nFile',
                  color: kAccentGreen,
                  onTap: () {
                    ref.read(documentsProvider.notifier).simulateUpload(
                        'NewDocument.pdf', FileExtension.pdf, 142.5);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Uploading file...',
                          style: GoogleFonts.poppins(
                            fontSize: 13,
                            color: kTextPrimary)),
                        backgroundColor: kSurface2Color,
                        duration: const Duration(seconds: 3),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _QuickActionCard(
                  icon: Icons.camera_alt_outlined,
                  label: 'Scan\nDocument',
                  color: kDocxColor,
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Camera scan coming soon',
                          style: GoogleFonts.poppins(
                            fontSize: 13,
                            color: kTextPrimary)),
                        backgroundColor: kSurface2Color,
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _QuickActionCard(
                  icon: Icons.search_rounded,
                  label: 'Search\nDocs',
                  color: kPptxColor,
                  onTap: () {
                    ref.read(currentScreenProvider.notifier).state = 3;
                  },
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _QuickActionCard(
                  icon: Icons.create_new_folder_outlined,
                  label: 'New\nFolder',
                  color: kStatusArchivedColor,
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Folders coming soon',
                          style: GoogleFonts.poppins(
                            fontSize: 13,
                            color: kTextPrimary)),
                        backgroundColor: kSurface2Color,
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 6),
            Text(
              label,
              style: GoogleFonts.poppins(
                color: color,
                fontSize: 10,
                fontWeight: FontWeight.w600,
                height: 1.3,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Recent Section ───────────────────────────────────────────────────────────
class _RecentSection extends StatelessWidget {
  final List<DocumentModel> documents;

  const _RecentSection({required this.documents});

  @override
  Widget build(BuildContext context) {
    if (documents.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 0, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Baru Ditambahkan',
                  style: GoogleFonts.poppins(
                    color: kTextPrimary,
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  'Lihat Semua →',
                  style: GoogleFonts.poppins(
                    color: kAccentGreen,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 130,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.only(right: 16),
              itemCount: documents.take(6).length,
              separatorBuilder: (_, __) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                final doc = documents[index];
                return _RecentCard(document: doc);
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _RecentCard extends StatelessWidget {
  final DocumentModel document;

  const _RecentCard({required this.document});

  @override
  Widget build(BuildContext context) {
    final color = getExtensionColor(document.extension);
    return Container(
      width: 110,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: kSurface2Color,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: kDividerColor),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: color.withOpacity(0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(_fileIcon(document.extension), color: color, size: 18),
          ),
          const SizedBox(height: 8),
          Text(
            document.fileName,
            style: GoogleFonts.poppins(
              color: kTextPrimary,
              fontSize: 11,
              fontWeight: FontWeight.w500,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const Spacer(),
          Text(
            DateFormat('dd MMM').format(document.dateModified),
            style: GoogleFonts.poppins(color: kTextTertiary, fontSize: 10),
          ),
        ],
      ),
    );
  }

  IconData _fileIcon(FileExtension ext) {
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

// ─── Starred Section ──────────────────────────────────────────────────────────
class _StarredSection extends StatelessWidget {
  final List<DocumentModel> documents;

  const _StarredSection({required this.documents});

  @override
  Widget build(BuildContext context) {
    if (documents.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 0, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: Row(
              children: [
                const Icon(Icons.star_rounded, color: Color(0xFFFFB300), size: 18),
                const SizedBox(width: 6),
                Text(
                  'Dokumen Favorit',
                  style: GoogleFonts.poppins(
                    color: kTextPrimary,
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 90,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.only(right: 16),
              itemCount: documents.take(5).length,
              separatorBuilder: (_, __) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                final doc = documents[index];
                final color = getExtensionColor(doc.extension);
                return Container(
                  width: 160,
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: kSurface2Color,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: const Color(0xFFFFB300).withOpacity(0.3),
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(_fileIcon(doc.extension), color: color, size: 16),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              doc.fileName,
                              style: GoogleFonts.poppins(
                                color: kTextPrimary,
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 3),
                            Text(
                              doc.formattedSize,
                              style: GoogleFonts.poppins(
                                  color: kTextTertiary, fontSize: 10),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.star_rounded,
                          color: Color(0xFFFFB300), size: 14),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  IconData _fileIcon(FileExtension ext) {
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

// ─── Storage Breakdown (Pie Chart) ───────────────────────────────────────────
class _StorageBreakdownSection extends StatefulWidget {
  final Map<FileExtension, double> breakdown;

  const _StorageBreakdownSection({required this.breakdown});

  @override
  State<_StorageBreakdownSection> createState() =>
      _StorageBreakdownSectionState();
}

class _StorageBreakdownSectionState extends State<_StorageBreakdownSection> {
  int _touchedIndex = -1;

  @override
  Widget build(BuildContext context) {
    if (widget.breakdown.isEmpty) return const SizedBox.shrink();

    final total = widget.breakdown.values.fold(0.0, (a, b) => a + b);
    final entries = widget.breakdown.entries.toList();

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Distribusi Tipe File',
            style: GoogleFonts.poppins(
              color: kTextPrimary,
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: kSurface2Color,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: kDividerColor),
            ),
            child: Row(
              children: [
                // Pie chart
                SizedBox(
                  height: 140,
                  width: 140,
                  child: PieChart(
                    PieChartData(
                      pieTouchData: PieTouchData(
                        touchCallback: (event, pieTouchResponse) {
                          setState(() {
                            if (!event.isInterestedForInteractions ||
                                pieTouchResponse == null ||
                                pieTouchResponse.touchedSection == null) {
                              _touchedIndex = -1;
                              return;
                            }
                            _touchedIndex = pieTouchResponse
                                .touchedSection!.touchedSectionIndex;
                          });
                        },
                      ),
                      sectionsSpace: 2,
                      centerSpaceRadius: 35,
                      sections: List.generate(entries.length, (i) {
                        final isTouched = i == _touchedIndex;
                        final ext = entries[i].key;
                        final size = entries[i].value;
                        final percent = size / total * 100;
                        return PieChartSectionData(
                          color: getExtensionColor(ext),
                          value: size,
                          title: isTouched ? '${percent.toStringAsFixed(0)}%' : '',
                          radius: isTouched ? 40 : 32,
                          titleStyle: GoogleFonts.poppins(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                          ),
                        );
                      }),
                    ),
                  ),
                ),

                const SizedBox(width: 16),

                // Legend
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: entries.map((entry) {
                      final percent = entry.value / total * 100;
                      final color = getExtensionColor(entry.key);
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Row(
                          children: [
                            Container(
                              width: 10,
                              height: 10,
                              decoration: BoxDecoration(
                                color: color,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                entry.key.name.toUpperCase(),
                                style: GoogleFonts.poppins(
                                  color: kTextSecondary,
                                  fontSize: 11,
                                ),
                              ),
                            ),
                            Text(
                              '${percent.toStringAsFixed(0)}%',
                              style: GoogleFonts.poppins(
                                color: kTextPrimary,
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
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
