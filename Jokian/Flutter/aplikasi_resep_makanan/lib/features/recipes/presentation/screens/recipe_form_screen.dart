// lib/features/recipes/presentation/screens/recipe_form_screen.dart

import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:file_picker/file_picker.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/recipes/data/models/category_model.dart';
import 'package:resepku/features/recipes/presentation/providers/recipes_provider.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

class RecipeFormScreen extends ConsumerStatefulWidget {
  const RecipeFormScreen({super.key});

  @override
  ConsumerState<RecipeFormScreen> createState() => _RecipeFormScreenState();
}

class _RecipeFormScreenState extends ConsumerState<RecipeFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descController = TextEditingController();

  String _difficulty = 'Mudah';
  CategoryModel? _selectedCategory;
  Uint8List? _imageBytes;
  String? _imageFileName;
  bool _isLoading = false;

  // Ingredients dynamic list
  final List<TextEditingController> _ingredientControllers = [TextEditingController()];

  // Steps dynamic list
  final List<Map<String, TextEditingController>> _stepControllers = [
    {'name': TextEditingController(), 'description': TextEditingController()}
  ];

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    for (final c in _ingredientControllers) c.dispose();
    for (final s in _stepControllers) {
      s['name']!.dispose();
      s['description']!.dispose();
    }
    super.dispose();
  }

  Future<void> _pickImage() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.image,
      allowMultiple: false,
      withData: true,
    );
    if (result != null && result.files.single.bytes != null) {
      setState(() {
        _imageBytes = result.files.single.bytes;
        _imageFileName = result.files.single.name;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCategory == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('Pilih kategori resep'), backgroundColor: AppColors.error, behavior: SnackBarBehavior.floating, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
      );
      return;
    }

    // Cek session aktif sebelum kirim
    final currentUser = Supabase.instance.client.auth.currentUser;
    if (currentUser == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Sesi habis. Silakan login ulang.'),
          backgroundColor: Colors.orange,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final ingredients = _ingredientControllers
          .map((c) => c.text.trim())
          .where((t) => t.isNotEmpty)
          .toList();

      final steps = _stepControllers
          .map((s) => {'name': s['name']!.text.trim(), 'description': s['description']!.text.trim()})
          .where((s) => s['description']!.isNotEmpty)
          .toList();

      await ref.read(recipeNotifierProvider.notifier).createRecipe(
            title: _titleController.text.trim(),
            description: _descController.text.trim(),
            ingredients: ingredients,
            steps: steps,
            difficulty: _difficulty,
            categoryId: _selectedCategory!.id,
            imageBytes: _imageBytes,
            imageFileName: _imageFileName,
          );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('✅ Resep terkirim! Menunggu persetujuan admin.'),
          backgroundColor: AppColors.primary,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      context.pop();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengirim: $e'), backgroundColor: AppColors.error, behavior: SnackBarBehavior.floating, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final categories = ref.watch(categoriesProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Submit Resep Baru'),
        backgroundColor: AppColors.background,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () => context.pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          physics: const BouncingScrollPhysics(),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ── Image Upload ────────────────────────────────
                _label('Foto Resep'),
                const SizedBox(height: 8),
                MouseRegion(
                  cursor: SystemMouseCursors.click,
                  child: GestureDetector(
                    onTap: _pickImage,
                    child: Container(
                      height: 180,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: _imageBytes != null ? AppColors.primary : AppColors.border,
                          width: 2,
                          style: BorderStyle.solid,
                        ),
                      ),
                      child: _imageBytes != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(14),
                              child: Image.memory(_imageBytes!, fit: BoxFit.cover),
                            )
                          : Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.camera_alt_outlined, size: 40, color: AppColors.textDisabled),
                                const SizedBox(height: 8),
                                Text('Tap untuk upload foto', style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppColors.textTertiary)),
                                Text('JPG, PNG, maks 5MB', style: Theme.of(context).textTheme.bodySmall),
                              ],
                            ),
                    ),
                  ),
                ),

                const SizedBox(height: 20),

                // ── Title ───────────────────────────────────────
                _label('Judul Resep *'),
                const SizedBox(height: 6),
                TextFormField(
                  controller: _titleController,
                  decoration: const InputDecoration(hintText: 'Mis: Ayam Bakar Madu Spesial'),
                  validator: (v) => (v == null || v.isEmpty) ? 'Judul wajib diisi' : null,
                ),
                const SizedBox(height: 16),

                // ── Description ─────────────────────────────────
                _label('Deskripsi'),
                const SizedBox(height: 6),
                TextFormField(
                  controller: _descController,
                  maxLines: 3,
                  decoration: const InputDecoration(hintText: 'Ceritakan tentang resep Anda...'),
                ),
                const SizedBox(height: 16),

                // ── Category ────────────────────────────────────
                _label('Kategori *'),
                const SizedBox(height: 6),
                categories.when(
                  data: (cats) => DropdownButtonFormField<CategoryModel>(
                    value: _selectedCategory,
                    hint: const Text('Pilih kategori'),
                    decoration: const InputDecoration(),
                    items: cats.map((cat) => DropdownMenuItem(value: cat, child: Text('${cat.emoji} ${cat.name}'))).toList(),
                    onChanged: (v) => setState(() => _selectedCategory = v),
                  ),
                  loading: () => const CircularProgressIndicator(),
                  error: (_, __) => const Text('Gagal memuat kategori'),
                ),
                const SizedBox(height: 16),

                // ── Difficulty ──────────────────────────────────
                _label('Tingkat Kesulitan *'),
                const SizedBox(height: 6),
                DropdownButtonFormField<String>(
                  value: _difficulty,
                  decoration: const InputDecoration(),
                  items: ['Mudah', 'Medium', 'Sulit']
                      .map((d) => DropdownMenuItem(value: d, child: Text(d)))
                      .toList(),
                  onChanged: (v) => setState(() => _difficulty = v ?? 'Mudah'),
                ),
                const SizedBox(height: 24),

                // ── Ingredients ─────────────────────────────────
                Row(
                  children: [
                    Expanded(child: _label('Bahan-Bahan *')),
                    TextButton.icon(
                      onPressed: () => setState(() => _ingredientControllers.add(TextEditingController())),
                      icon: const Icon(Icons.add_rounded, size: 16),
                      label: const Text('Tambah'),
                      style: TextButton.styleFrom(foregroundColor: AppColors.primary),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                ..._ingredientControllers.asMap().entries.map((e) {
                  final i = e.key;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(color: AppColors.primaryContainer, shape: BoxShape.circle),
                          child: Center(child: Text('${i + 1}', style: const TextStyle(color: AppColors.primary, fontSize: 12, fontWeight: FontWeight.w700))),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: TextFormField(
                            controller: e.value,
                            decoration: InputDecoration(hintText: 'Mis: 2 cups tepung terigu'),
                            validator: i == 0 ? (v) => (v == null || v.isEmpty) ? 'Minimal 1 bahan' : null : null,
                          ),
                        ),
                        if (i > 0)
                          IconButton(
                            icon: const Icon(Icons.remove_circle_outline, color: AppColors.error),
                            onPressed: () => setState(() => _ingredientControllers.removeAt(i)),
                          ),
                      ],
                    ),
                  );
                }),

                const SizedBox(height: 24),

                // ── Steps ───────────────────────────────────────
                Row(
                  children: [
                    Expanded(child: _label('Langkah Memasak *')),
                    TextButton.icon(
                      onPressed: () => setState(() => _stepControllers.add({'name': TextEditingController(), 'description': TextEditingController()})),
                      icon: const Icon(Icons.add_rounded, size: 16),
                      label: const Text('Tambah'),
                      style: TextButton.styleFrom(foregroundColor: AppColors.primary),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                ..._stepControllers.asMap().entries.map((e) {
                  final i = e.key;
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 28, height: 28,
                              decoration: const BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                              child: Center(child: Text('${i + 1}', style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700))),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: TextFormField(
                                controller: e.value['name'],
                                decoration: const InputDecoration(hintText: 'Nama langkah (mis: Tumis Bumbu)'),
                              ),
                            ),
                            if (i > 0)
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline, color: AppColors.error, size: 20),
                                onPressed: () => setState(() => _stepControllers.removeAt(i)),
                              ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: e.value['description'],
                          maxLines: 2,
                          decoration: const InputDecoration(hintText: 'Jelaskan langkah ini secara detail...'),
                          validator: i == 0 ? (v) => (v == null || v.isEmpty) ? 'Minimal 1 langkah' : null : null,
                        ),
                      ],
                    ),
                  );
                }),

                const SizedBox(height: 32),

                // ── Submit Button ───────────────────────────────
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    child: _isLoading
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Kirim Resep untuk Direview'),
                  ),
                ),
                const SizedBox(height: 8),
                Center(
                  child: Text(
                    'Resep akan ditinjau admin sebelum dipublikasikan',
                    style: Theme.of(context).textTheme.bodySmall,
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _label(String text) {
    return Text(text, style: Theme.of(context).textTheme.labelLarge);
  }
}
