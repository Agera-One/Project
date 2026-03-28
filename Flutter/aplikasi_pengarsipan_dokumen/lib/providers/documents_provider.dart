// lib/providers/documents_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/document_model.dart';
import '../models/mock_data.dart';

// ─── Sort Options ─────────────────────────────────────────────────────────────
enum SortOption { nameAsc, nameDesc, dateNewest, dateOldest, sizeDesc, sizeAsc }

// ─── Documents State ──────────────────────────────────────────────────────────
class DocumentsState {
  final List<DocumentModel> documents;
  final String searchQuery;
  final SortOption sortOption;
  final bool isLoading;
  final bool isUploading;
  final double uploadProgress;

  const DocumentsState({
    required this.documents,
    this.searchQuery = '',
    this.sortOption = SortOption.dateNewest,
    this.isLoading = false,
    this.isUploading = false,
    this.uploadProgress = 0.0,
  });

  DocumentsState copyWith({
    List<DocumentModel>? documents,
    String? searchQuery,
    SortOption? sortOption,
    bool? isLoading,
    bool? isUploading,
    double? uploadProgress,
  }) {
    return DocumentsState(
      documents: documents ?? this.documents,
      searchQuery: searchQuery ?? this.searchQuery,
      sortOption: sortOption ?? this.sortOption,
      isLoading: isLoading ?? this.isLoading,
      isUploading: isUploading ?? this.isUploading,
      uploadProgress: uploadProgress ?? this.uploadProgress,
    );
  }

  // ─── Filtered & Sorted Lists ───────────────────────────────────────────────
  List<DocumentModel> _applySearchAndSort(List<DocumentModel> list) {
    var result = list.where((doc) {
      if (searchQuery.isEmpty) return true;
      return doc.fileName.toLowerCase().contains(searchQuery.toLowerCase());
    }).toList();

    switch (sortOption) {
      case SortOption.nameAsc:
        result.sort((a, b) => a.fileName.compareTo(b.fileName));
      case SortOption.nameDesc:
        result.sort((a, b) => b.fileName.compareTo(a.fileName));
      case SortOption.dateNewest:
        result.sort((a, b) => b.dateModified.compareTo(a.dateModified));
      case SortOption.dateOldest:
        result.sort((a, b) => a.dateModified.compareTo(b.dateModified));
      case SortOption.sizeDesc:
        result.sort((a, b) => b.sizeKb.compareTo(a.sizeKb));
      case SortOption.sizeAsc:
        result.sort((a, b) => a.sizeKb.compareTo(b.sizeKb));
    }

    return result;
  }

  List<DocumentModel> get myDocuments => _applySearchAndSort(
      documents.where((d) => d.status == DocumentStatus.active).toList());

  List<DocumentModel> get starredDocuments =>
      _applySearchAndSort(documents.where((d) => d.isStarred).toList());

  List<DocumentModel> get archivedDocuments => _applySearchAndSort(
      documents.where((d) => d.status == DocumentStatus.archived).toList());

  List<DocumentModel> get trashedDocuments => _applySearchAndSort(
      documents.where((d) => d.status == DocumentStatus.deleted).toList());

  List<DocumentModel> get recentDocuments {
    final all = documents.where((d) => d.status != DocumentStatus.deleted).toList();
    all.sort((a, b) => b.dateModified.compareTo(a.dateModified));
    return all.take(20).toList();
  }

  // Storage Stats
  double get totalStorageKb =>
      documents.where((d) => d.status == DocumentStatus.active).fold(0, (sum, d) => sum + d.sizeKb);

  double get totalStorageMb => totalStorageKb / 1024;
  double get maxStorageGb => 15.0;
  double get usedStorageGb => totalStorageMb / 1024;
  double get storagePercent => usedStorageGb / maxStorageGb;

  int get totalDocumentCount =>
      documents.where((d) => d.status == DocumentStatus.active).length;

  int get starredCount => documents.where((d) => d.isStarred).length;

  int get newThisWeek {
    final weekAgo = DateTime.now().subtract(const Duration(days: 7));
    return documents
        .where((d) =>
            d.status == DocumentStatus.active &&
            d.dateModified.isAfter(weekAgo))
        .length;
  }

  // File type breakdown for chart
  Map<FileExtension, double> get storageByType {
    final Map<FileExtension, double> result = {};
    for (final doc in documents.where((d) => d.status == DocumentStatus.active)) {
      result[doc.extension] = (result[doc.extension] ?? 0) + doc.sizeKb;
    }
    return result;
  }
}

// ─── Documents Notifier ───────────────────────────────────────────────────────
class DocumentsNotifier extends StateNotifier<DocumentsState> {
  DocumentsNotifier()
      : super(DocumentsState(documents: List.from(mockDocuments)));

  void toggleStar(String id) {
    final docs = state.documents.map((d) {
      if (d.id == id) return d.copyWith(isStarred: !d.isStarred);
      return d;
    }).toList();
    state = state.copyWith(documents: docs);
  }

  void archiveDocument(String id) {
    final docs = state.documents.map((d) {
      if (d.id == id) {
        return d.copyWith(status: DocumentStatus.archived, isStarred: false);
      }
      return d;
    }).toList();
    state = state.copyWith(documents: docs);
  }

  void restoreDocument(String id) {
    final docs = state.documents.map((d) {
      if (d.id == id) return d.copyWith(status: DocumentStatus.active);
      return d;
    }).toList();
    state = state.copyWith(documents: docs);
  }

  void moveToTrash(String id) {
    final docs = state.documents.map((d) {
      if (d.id == id) {
        return d.copyWith(status: DocumentStatus.deleted, isStarred: false);
      }
      return d;
    }).toList();
    state = state.copyWith(documents: docs);
  }

  void deletePermanently(String id) {
    final docs = state.documents.where((d) => d.id != id).toList();
    state = state.copyWith(documents: docs);
  }

  void setSearch(String query) {
    state = state.copyWith(searchQuery: query);
  }

  void setSort(SortOption option) {
    state = state.copyWith(sortOption: option);
  }

  void addDocument(DocumentModel doc) {
    state = state.copyWith(documents: [...state.documents, doc]);
  }

  Future<void> simulateUpload(String fileName, FileExtension ext, double sizeKb) async {
    state = state.copyWith(isUploading: true, uploadProgress: 0.0);
    for (int i = 1; i <= 10; i++) {
      await Future.delayed(const Duration(milliseconds: 200));
      state = state.copyWith(uploadProgress: i / 10);
    }
    final newDoc = DocumentModel(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      fileName: fileName,
      ownerName: 'Dzaki Prasetyo',
      ownerEmail: 'dzakiprasetyo98@gmail.com',
      ownerInitials: 'DP',
      extension: ext,
      sizeKb: sizeKb,
      dateModified: DateTime.now(),
      status: DocumentStatus.active,
    );
    addDocument(newDoc);
    state = state.copyWith(isUploading: false, uploadProgress: 0.0);
  }
}

// ─── Provider ─────────────────────────────────────────────────────────────────
final documentsProvider =
    StateNotifierProvider<DocumentsNotifier, DocumentsState>(
  (ref) => DocumentsNotifier(),
);
