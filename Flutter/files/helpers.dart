// lib/utils/helpers.dart
import 'package:flutter/material.dart';
import '../models/document_model.dart';
import '../theme.dart';

Color getExtensionColor(FileExtension extension) {
  switch (extension) {
    case FileExtension.png:
      return kPngColor;
    case FileExtension.jpg:
      return kJpgColor;
    case FileExtension.pdf:
      return kPdfColor;
    case FileExtension.docx:
      return kDocxColor;
    case FileExtension.pptx:
      return kPptxColor;
    case FileExtension.xlsx:
      return kXlsxColor;
    case FileExtension.txt:
      return kTxtColor;
    case FileExtension.other:
      return kTextTertiary;
  }
}

FileExtension extensionFromString(String name) {
  final lower = name.toLowerCase();
  if (lower.endsWith('.pdf')) return FileExtension.pdf;
  if (lower.endsWith('.png')) return FileExtension.png;
  if (lower.endsWith('.jpg') || lower.endsWith('.jpeg')) return FileExtension.jpg;
  if (lower.endsWith('.docx') || lower.endsWith('.doc')) return FileExtension.docx;
  if (lower.endsWith('.pptx') || lower.endsWith('.ppt')) return FileExtension.pptx;
  if (lower.endsWith('.xlsx') || lower.endsWith('.xls') || lower.endsWith('.csv')) {
    return FileExtension.xlsx;
  }
  if (lower.endsWith('.txt')) return FileExtension.txt;
  return FileExtension.other;
}

String formatStorageSize(double gb) {
  if (gb < 0.001) return '${(gb * 1024 * 1024).toStringAsFixed(0)} KB';
  if (gb < 1) return '${(gb * 1024).toStringAsFixed(1)} MB';
  return '${gb.toStringAsFixed(2)} GB';
}
