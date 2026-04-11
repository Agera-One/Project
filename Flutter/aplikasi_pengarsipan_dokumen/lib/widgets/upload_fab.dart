// lib/widgets/upload_fab.dart
import 'dart:io';
import 'dart:typed_data';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/document_model.dart';
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
        withData: true,           // ← WAJIB diubah jadi true untuk Web
      );

      if (result == null || result.files.isEmpty) return;

      final PlatformFile file = result.files.first;

      // Ambil bytes (ini yang paling penting untuk cross-platform)
      Uint8List? bytes = file.bytes;

      // Fallback untuk platform mobile (jika bytes null)
      if (bytes == null && file.path != null) {
        bytes = await File(file.path!).readAsBytes();
      }

      if (bytes == null) {
        throw Exception('Tidak dapat membaca data file');
      }

      final ext = extensionFromString(file.name);
      final sizeKb = (file.size / 1024).toDouble();

      final nameWithoutExt = file.name.contains('.')
          ? file.name.substring(0, file.name.lastIndexOf('.'))
          : file.name;

      // Panggil method upload di provider
      await ref.read(documentsProvider.notifier).uploadDocument(
            fileName: nameWithoutExt,
            ext: ext,
            sizeKb: sizeKb,
            fileBytes: bytes,
            mimeType: _mimeFromExtension(ext),
          );

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
                    '"$nameWithoutExt" berhasil diupload!',
                    style: GoogleFonts.poppins(
                        fontSize: 13, color: kTextPrimary),
                  ),
                ),
              ],
            ),
            backgroundColor: kSurface2Color,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      print('Upload error: $e');
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Upload gagal: ${e.toString()}',
              style: GoogleFonts.poppins(fontSize: 13, color: kTextPrimary),
            ),
            backgroundColor: kPdfColor.withOpacity(0.9),
          ),
        );
      }
    }
  }

  String _mimeFromExtension(FileExtension ext) {
    switch (ext) {
      case FileExtension.pdf:
        return 'application/pdf';
      case FileExtension.png:
        return 'image/png';
      case FileExtension.jpg:
        return 'image/jpeg';
      case FileExtension.docx:
        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
      case FileExtension.pptx:
        return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
      case FileExtension.xlsx:
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      case FileExtension.txt:
        return 'text/plain';
      default:
        return 'application/octet-stream';
    }
  }
}