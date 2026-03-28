document_archiver/
├── pubspec.yaml                          ← Dependencies semua packages
├── lib/
│   ├── main.dart                         ← Entry point + SystemUI setup
│   ├── app.dart                          ← Shell utama + NavigationDrawer + BottomNav
│   ├── theme.dart                        ← Semua warna persis dari screenshot
│   ├── models/
│   │   ├── document_model.dart           ← Model + enum Status & Extension
│   │   └── mock_data.dart                ← 20 dokumen mock dari screenshot
│   ├── providers/
│   │   └── documents_provider.dart       ← Riverpod state (filter, sort, actions)
│   ├── screens/
│   │   ├── dashboard_screen.dart         ← Home + stat cards + chart pie
│   │   └── documents_screens.dart        ← My Docs, Starred, Archives, Trash, Recently
│   ├── widgets/
│   │   ├── document_card.dart            ← Card tabel mirip web + bottom sheet detail
│   │   ├── app_drawer.dart               ← Sidebar drawer + user profile
│   │   ├── upload_fab.dart               ← FAB hijau + file_picker + progress
│   │   ├── search_sort_bar.dart          ← Search real-time + sort dropdown
│   │   └── storage_progress.dart         ← Linear progress storage
│   └── utils/
│       └── helpers.dart                  ← Extension color mapper + format helpers