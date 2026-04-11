// lib/models/document_model.dart
enum DocumentStatus { active, archived, deleted }

enum FileExtension { pdf, png, docx, pptx, xlsx, jpg, txt, other }

class DocumentModel {
  final String id;
  final String fileName;
  final String ownerName;
  final String ownerEmail;
  final String ownerInitials;
  final String filePath;
  final FileExtension extension;
  final double sizeKb;
  final DateTime dateModified;
  final DocumentStatus status;
  final bool isStarred;

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
    required this.filePath,
    this.isStarred = false,
  });

  factory DocumentModel.fromJson(Map<String, dynamic> json) {
    return DocumentModel(
      id: json['id'] as String,
      fileName: json['file_name'] as String,
      ownerName: json['owner_name'] as String? ?? '',
      ownerEmail: json['owner_email'] as String? ?? '',
      ownerInitials: json['owner_initials'] as String? ?? '??',
      extension: _parseExtension(json['extension'] as String? ?? 'other'),
      sizeKb: (json['size_kb'] as num).toDouble(),
      dateModified: DateTime.parse(json['date_modified'] as String),
      status: _parseStatus(json['status'] as String? ?? 'active'),
      isStarred: json['is_starred'] as bool? ?? false,
      filePath: json['file_path'] as String? ?? '',
    );
  }

  DocumentModel copyWith({
    String? id,
    String? fileName,
    String? ownerName,
    String? ownerEmail,
    String? ownerInitials,
    String? filePath,
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
      filePath: filePath ?? this.filePath,
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
      case FileExtension.pdf: return 'PDF';
      case FileExtension.png: return 'PNG';
      case FileExtension.docx: return 'DOCX';
      case FileExtension.pptx: return 'PPTX';
      case FileExtension.xlsx: return 'XLSX';
      case FileExtension.jpg: return 'JPG';
      case FileExtension.txt: return 'TXT';
      case FileExtension.other: return 'FILE';
    }
  }
}

FileExtension _parseExtension(String s) {
  return FileExtension.values.firstWhere(
    (e) => e.name.toLowerCase() == s.toLowerCase(),
    orElse: () => FileExtension.other,
  );
}

DocumentStatus _parseStatus(String s) {
  return DocumentStatus.values.firstWhere(
    (e) => e.name.toLowerCase() == s.toLowerCase(),
    orElse: () => DocumentStatus.active,
  );
}