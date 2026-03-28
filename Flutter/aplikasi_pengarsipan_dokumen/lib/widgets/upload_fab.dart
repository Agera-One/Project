// lib/widgets/upload_fab.dart
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';
import '../utils/helpers.dart';

class UploadFab extends ConsumerWidget {
  const UploadFab({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(documentsProvider);

    if (state.isUploading) {
      return FloatingActionButton.extended(
        onPressed: null,
        backgroundColor: kAccentGreen.withOpacity(0.6),
        label: Row(
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                value: state.uploadProgress,
                color: Colors.black,
                strokeWidth: 2.5,
              ),
            ),
            const SizedBox(width: 10),
            Text(
              'Uploading ${(state.uploadProgress * 100).toStringAsFixed(0)}%',
              style: GoogleFonts.poppins(
                color: Colors.black,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      );
    }

    return FloatingActionButton.extended(
      onPressed: () => _pickFile(context, ref),
      backgroundColor: kAccentGreen,
      icon: const Icon(Icons.add_rounded, color: Colors.black, size: 22),
      label: Text(
        'Upload',
        style: GoogleFonts.poppins(
          color: Colors.black,
          fontSize: 13,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Future<void> _pickFile(BuildContext context, WidgetRef ref) async {
    try {
      final result = await FilePicker.platform.pickFiles(
        allowMultiple: false,
        type: FileType.any,
      );

      if (result != null && result.files.isNotEmpty) {
        final file = result.files.first;
        final ext = extensionFromString(file.name);
        final sizeKb = (file.size / 1024).toDouble();

        // Remove extension from display name
        final nameWithoutExt = file.name.contains('.')
            ? file.name.substring(0, file.name.lastIndexOf('.'))
            : file.name;

        await ref
            .read(documentsProvider.notifier)
            .simulateUpload(nameWithoutExt, ext, sizeKb);

        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Row(
                children: [
                  const Icon(Icons.check_circle_rounded,
                      color: kAccentGreen, size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '"$nameWithoutExt" uploaded successfully!',
                      style: GoogleFonts.poppins(
                        fontSize: 13,
                        color: kTextPrimary),
                    ),
                  ),
                ],
              ),
              backgroundColor: kSurface2Color,
              duration: const Duration(seconds: 3),
            ),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Upload failed: $e',
                style: GoogleFonts.poppins(fontSize: 13)),
            backgroundColor: kPdfColor.withOpacity(0.8),
          ),
        );
      }
    }
  }
}
