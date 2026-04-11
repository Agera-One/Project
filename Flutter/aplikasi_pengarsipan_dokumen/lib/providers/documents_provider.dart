// lib/providers/documents_provider.dart
import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:uuid/uuid.dart'; 
import '../models/document_model.dart';

enum SortOption { nameAsc, nameDesc, dateNewest, dateOldest, sizeDesc, sizeAsc }

// ── Supabase client accessor ──────────────────────────────────
SupabaseClient get _db => Supabase.instance.client;

// ── Helper: cek user login, lempar error jika tidak ──────────
User get _requireUser {
  final user = _db.auth.currentUser;
  if (user == null) throw Exception('User tidak login. Silakan login terlebih dahulu.');
  return user;
}

// ── Map DB row → DocumentModel ────────────────────────────────
DocumentModel _fromRow(Map<String, dynamic> row) {
  return DocumentModel(
    id: row['id'] as String,
    fileName: row['file_name'] as String,
    ownerName: row['owner_name'] as String? ?? '',
    ownerEmail: row['owner_email'] as String? ?? '',
    ownerInitials: row['owner_initials'] as String? ?? '',
    extension: _parseExtension(row['extension'] as String? ?? 'other'),
    sizeKb: (row['size_kb'] as num).toDouble(),
    dateModified: DateTime.parse(row['date_modified'] as String),
    status: _parseStatus(row['status'] as String? ?? 'active'),
    isStarred: row['is_starred'] as bool? ?? false,
    filePath: row['file_path'] as String? ?? '',
  );
}

FileExtension _parseExtension(String s) {
  return FileExtension.values.firstWhere(
    (e) => e.name == s,
    orElse: () => FileExtension.other,
  );
}

DocumentStatus _parseStatus(String s) {
  return DocumentStatus.values.firstWhere(
    (e) => e.name == s,
    orElse: () => DocumentStatus.active,
  );
}

// ── DocumentsState ────────────────────────────────────────────
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
  }) =>
      DocumentsState(
        documents: documents ?? this.documents,
        searchQuery: searchQuery ?? this.searchQuery,
        sortOption: sortOption ?? this.sortOption,
        isLoading: isLoading ?? this.isLoading,
        isUploading: isUploading ?? this.isUploading,
        uploadProgress: uploadProgress ?? this.uploadProgress,
      );

  // ── Filter & Sort helpers ─────────────────────────────────
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
    final all = documents
        .where((d) => d.status != DocumentStatus.deleted)
        .toList();
    all.sort((a, b) => b.dateModified.compareTo(a.dateModified));
    return all.take(20).toList();
  }

  double get totalStorageKb => documents
      .where((d) => d.status == DocumentStatus.active)
      .fold(0, (sum, d) => sum + d.sizeKb);
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

  Map<FileExtension, double> get storageByType {
    final Map<FileExtension, double> result = {};
    for (final doc
        in documents.where((d) => d.status == DocumentStatus.active)) {
      result[doc.extension] = (result[doc.extension] ?? 0) + doc.sizeKb;
    }
    return result;
  }
}

// ── DocumentsNotifier ─────────────────────────────────────────
class DocumentsNotifier extends StateNotifier<DocumentsState> {
  RealtimeChannel? _channel;

  DocumentsNotifier() : super(const DocumentsState(documents: [])) {
    // Hanya fetch jika user sudah login
    if (_db.auth.currentUser != null) {
      fetchDocuments();
      _subscribeRealtime();
    }
  }

  // ── Fetch documents ───────────────────────────────────────
  Future<void> fetchDocuments() async {
    // Guard: jangan fetch jika belum login
    final userId = _db.auth.currentUser?.id;
    if (userId == null) {
      state = state.copyWith(documents: [], isLoading: false);
      return;
    }

    state = state.copyWith(isLoading: true);
    try {
      final rows = await _db
          .from('documents')
          .select()
          .eq('owner_id', userId)
          .order('date_modified', ascending: false);

      final docs = (rows as List).map((r) => _fromRow(r)).toList();
      state = state.copyWith(documents: docs, isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false);
    }
  }

  // ── Reset state (dipanggil saat logout) ───────────────────
  void resetState() {
    _channel?.unsubscribe();
    _channel = null;
    state = const DocumentsState(documents: []);
  }

  // ── Toggle Star ───────────────────────────────────────────
  Future<void> toggleStar(String id) async {
    _requireUser; // guard
    final doc = state.documents.firstWhere((d) => d.id == id);
    final newVal = !doc.isStarred;
    _updateLocal(id, (d) => d.copyWith(isStarred: newVal));
    await _db
        .from('documents')
        .update({'is_starred': newVal}).eq('id', id);
  }

  // ── Archive ───────────────────────────────────────────────
  Future<void> archiveDocument(String id) async {
    _requireUser;
    _updateLocal(id, (d) => d.copyWith(
        status: DocumentStatus.archived, isStarred: false));
    await _db.from('documents').update({
      'status': 'archived',
      'is_starred': false,
      'date_modified': DateTime.now().toIso8601String(),
    }).eq('id', id);
  }

  // ── Restore ───────────────────────────────────────────────
  Future<void> restoreDocument(String id) async {
    _requireUser;
    _updateLocal(id, (d) => d.copyWith(status: DocumentStatus.active));
    await _db.from('documents').update({
      'status': 'active',
      'date_modified': DateTime.now().toIso8601String(),
    }).eq('id', id);
  }

  // ── Move to Trash ─────────────────────────────────────────
  Future<void> moveToTrash(String id) async {
    _requireUser;
    _updateLocal(id, (d) => d.copyWith(
        status: DocumentStatus.deleted, isStarred: false));
    await _db.from('documents').update({
      'status': 'deleted',
      'is_starred': false,
      'date_modified': DateTime.now().toIso8601String(),
    }).eq('id', id);
  }

  // ── Permanent Delete ──────────────────────────────────────
  Future<void> deletePermanently(String id) async {
    _requireUser;
    final doc = state.documents.firstWhere((d) => d.id == id,
        orElse: () => throw Exception('Document not found'));

    state = state.copyWith(
        documents: state.documents.where((d) => d.id != id).toList());

    if (doc.filePath.isNotEmpty) {
      await _db.storage.from('documents').remove([doc.filePath]);
    }
    await _db.from('documents').delete().eq('id', id);
  }

  // ── Upload Document ───────────────────────────────────────
  Future<void> uploadDocument({
    required String fileName,
    required FileExtension ext,
    required double sizeKb,
    required List<int> fileBytes,
    required String mimeType,
  }) async {
    final user = _requireUser; // guard — lempar error jika tidak login

    state = state.copyWith(isUploading: true, uploadProgress: 0.0);
    try {
      final docId = const Uuid().v4();
      final extStr = ext.name;
      final originalName = '$fileName.$extStr';
      final storagePath = '${user.id}/$docId/$originalName';

      state = state.copyWith(uploadProgress: 0.3);

      await _db.storage.from('documents').uploadBinary(
        storagePath,
        Uint8List.fromList(fileBytes),
        fileOptions: FileOptions(contentType: mimeType, upsert: false),
      );

      state = state.copyWith(uploadProgress: 0.7);

      final fullName = user.userMetadata?['full_name'] as String? ??
          user.email?.split('@').first ??
          'User';
      final initials = user.userMetadata?['initials'] as String? ??
          _initials(fullName);

      final row = await _db.from('documents').insert({
        'id': docId,
        'file_name': fileName,
        'owner_id': user.id,
        'owner_name': fullName,
        'owner_email': user.email ?? '',
        'owner_initials': initials,
        'extension': extStr,
        'size_kb': sizeKb,
        'date_modified': DateTime.now().toIso8601String(),
        'status': 'active',
        'is_starred': false,
        'file_path': storagePath,
      }).select().single();

      final newDoc = _fromRow(row);
      state = state.copyWith(
        documents: [newDoc, ...state.documents],
        isUploading: false,
        uploadProgress: 1.0,
      );
      await Future.delayed(const Duration(milliseconds: 300));
      state = state.copyWith(uploadProgress: 0.0);
    } catch (e) {
      state = state.copyWith(isUploading: false, uploadProgress: 0.0);
      rethrow;
    }
  }

  // ── Real-time subscription ────────────────────────────────
  void _subscribeRealtime() {
    final userId = _db.auth.currentUser?.id;
    if (userId == null) return;

    _channel = _db
        .channel('documents-changes')
        .onPostgresChanges(
          event: PostgresChangeEvent.all,
          schema: 'public',
          table: 'documents',
          filter: PostgresChangeFilter(
            type: PostgresChangeFilterType.eq,
            column: 'owner_id',
            value: userId,
          ),
          callback: (payload) => fetchDocuments(),
        )
        .subscribe();
  }

  // ── Search & Sort ─────────────────────────────────────────
  void setSearch(String query) =>
      state = state.copyWith(searchQuery: query);

  void setSort(SortOption option) =>
      state = state.copyWith(sortOption: option);

  void addDocument(DocumentModel doc) =>
      state = state.copyWith(documents: [doc, ...state.documents]);

  // ── Internal helpers ──────────────────────────────────────
  void _updateLocal(
      String id, DocumentModel Function(DocumentModel) updater) {
    state = state.copyWith(
      documents:
          state.documents.map((d) => d.id == id ? updater(d) : d).toList(),
    );
  }

  String _initials(String name) {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name.substring(0, name.length >= 2 ? 2 : 1).toUpperCase();
  }

  @override
  void dispose() {
    _channel?.unsubscribe();
    super.dispose();
  }
}

// ── Provider ──────────────────────────────────────────────────
final documentsProvider =
    StateNotifierProvider<DocumentsNotifier, DocumentsState>(
  (ref) {
    final notifier = DocumentsNotifier();

    // Reset dokumen saat user logout
    ref.listen(
      // listen perubahan auth session
      Provider<String?>((r) =>
          Supabase.instance.client.auth.currentUser?.id),
      (previous, next) {
        if (next == null) {
          // User logout → bersihkan state
          notifier.resetState();
        } else if (previous == null && next != null) {
          // User baru login → fetch data
          notifier.fetchDocuments();
        }
      },
    );

    return notifier;
  },
);
