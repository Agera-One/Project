// lib/widgets/search_sort_bar.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/documents_provider.dart';
import '../theme.dart';

class SearchSortBar extends ConsumerStatefulWidget {
  const SearchSortBar({super.key});

  @override
  ConsumerState<SearchSortBar> createState() => _SearchSortBarState();
}

class _SearchSortBarState extends ConsumerState<SearchSortBar> {
  final _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(documentsProvider);
    final notifier = ref.read(documentsProvider.notifier);

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Row(
        children: [
          // Search
          Expanded(
            child: TextField(
              controller: _controller,
              onChanged: notifier.setSearch,
              style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Search Documents...',
                prefixIcon: const Icon(Icons.search_rounded, size: 20),
                suffixIcon: _controller.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded, size: 18),
                        onPressed: () {
                          _controller.clear();
                          notifier.setSearch('');
                        },
                      )
                    : null,
              ),
            ),
          ),

          const SizedBox(width: 8),

          // Sort button
          PopupMenuButton<SortOption>(
            color: kSurface2Color,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: kSurface2Color,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  const Icon(Icons.sort_rounded, color: kTextSecondary, size: 18),
                  const SizedBox(width: 4),
                  Text(
                    'Sort',
                    style: GoogleFonts.poppins(
                      color: kTextSecondary,
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            onSelected: notifier.setSort,
            itemBuilder: (context) => [
              _sortItem('Name (A–Z)', SortOption.nameAsc, state.sortOption),
              _sortItem('Name (Z–A)', SortOption.nameDesc, state.sortOption),
              _sortItem('Newest First', SortOption.dateNewest, state.sortOption),
              _sortItem('Oldest First', SortOption.dateOldest, state.sortOption),
              _sortItem('Largest First', SortOption.sizeDesc, state.sortOption),
              _sortItem('Smallest First', SortOption.sizeAsc, state.sortOption),
            ],
          ),
        ],
      ),
    );
  }

  PopupMenuItem<SortOption> _sortItem(
      String label, SortOption option, SortOption current) {
    final isSelected = option == current;
    return PopupMenuItem(
      value: option,
      child: Row(
        children: [
          Icon(
            isSelected ? Icons.check_rounded : Icons.radio_button_unchecked,
            color: isSelected ? kAccentGreen : kTextTertiary,
            size: 16,
          ),
          const SizedBox(width: 8),
          Text(
            label,
            style: GoogleFonts.poppins(
              color: isSelected ? kAccentGreen : kTextPrimary,
              fontSize: 13,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
            ),
          ),
        ],
      ),
    );
  }
}
