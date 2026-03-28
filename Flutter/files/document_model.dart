// lib/models/document_model.dart

enum DocumentStatus { active, archived, deleted }

enum FileExtension { pdf, png, docx, pptx, xlsx, jpg, txt, other }

class DocumentModel {
  final String id;
  final String fileName;
  final String ownerName;
  final String ownerEmail;
  final String ownerInitials;
  final FileExtension extension;
  final double sizeKb; // in KB
  final DateTime dateModified;
  final DocumentStatus status;
  bool isStarred;

  DocumentModel({
    required this.id,
    required this.fileName,
    required this.ownerName,
    required this.ownerEmail,
    required this.ownerInitials,
    required this.extension,
    required this.sizeKb,
    required this.dateModified,
    required this.status,
    this.isStarred = false,
  });

  DocumentModel copyWith({
    String? id,
    String? fileName,
    String? ownerName,
    String? ownerEmail,
    String? ownerInitials,
    FileExtension? extension,
    double? sizeKb,
    DateTime? dateModified,
    DocumentStatus? status,
    bool? isStarred,
  }) {
    return DocumentModel(
      id: id ?? this.id,
      fileName: fileName ?? this.fileName,
      ownerName: ownerName ?? this.ownerName,
      ownerEmail: ownerEmail ?? this.ownerEmail,
      ownerInitials: ownerInitials ?? this.ownerInitials,
      extension: extension ?? this.extension,
      sizeKb: sizeKb ?? this.sizeKb,
      dateModified: dateModified ?? this.dateModified,
      status: status ?? this.status,
      isStarred: isStarred ?? this.isStarred,
    );
  }

  String get formattedSize {
    if (sizeKb >= 1024) {
      return '${(sizeKb / 1024).toStringAsFixed(1)} MB';
    }
    return '${sizeKb.toStringAsFixed(1)} KB';
  }

  String get extensionString {
    switch (extension) {
      case FileExtension.pdf:
        return 'PDF';
      case FileExtension.png:
        return 'PNG';
      case FileExtension.docx:
        return 'DOCX';
      case FileExtension.pptx:
        return 'PPTX';
      case FileExtension.xlsx:
        return 'XLSX';
      case FileExtension.jpg:
        return 'JPG';
      case FileExtension.txt:
        return 'TXT';
      case FileExtension.other:
        return 'FILE';
    }
  }
}
