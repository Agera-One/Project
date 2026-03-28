// lib/widgets/storage_progress.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme.dart';

class StorageProgressWidget extends StatelessWidget {
  final double usedGb;
  final double maxGb;

  const StorageProgressWidget({
    super.key,
    required this.usedGb,
    required this.maxGb,
  });

  @override
  Widget build(BuildContext context) {
    final percent = (usedGb / maxGb).clamp(0.0, 1.0);
    final usedMb = usedGb * 1024;

    Color progressColor;
    if (percent < 0.6) {
      progressColor = kAccentGreenDark;
    } else if (percent < 0.85) {
      progressColor = kStatusArchivedColor;
    } else {
      progressColor = kPdfColor;
    }

    String usedLabel;
    if (usedMb < 1024) {
      usedLabel = '${usedMb.toStringAsFixed(0)} MB';
    } else {
      usedLabel = '${usedGb.toStringAsFixed(2)} GB';
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: kSurface2Color,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: kDividerColor),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Storage Usage',
                style: GoogleFonts.poppins(
                  color: kTextSecondary,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                '$usedLabel / ${maxGb.toStringAsFixed(0)} GB',
                style: GoogleFonts.poppins(
                  color: kTextPrimary,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: percent,
              backgroundColor: kDividerColor,
              valueColor: AlwaysStoppedAnimation<Color>(progressColor),
              minHeight: 8,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            '${(percent * 100).toStringAsFixed(1)}% used',
            style: GoogleFonts.poppins(
              color: kTextTertiary,
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }
}
