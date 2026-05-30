// lib/features/profile/presentation/screens/profile_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/core/theme/app_theme.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  final bool isAdmin;
  const ProfileScreen({super.key, required this.isAdmin});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _nameController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _isEditing = false;
  bool _isSaving = false;

  @override
  void dispose() {
    _nameController.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);
    try {
      await ref.read(authNotifierProvider.notifier).updateProfile(
            fullName: _nameController.text.trim(),
          );
      ref.invalidate(currentProfileProvider);
      if (!mounted) return;
      setState(() => _isEditing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: const Text('✅ Profil berhasil diperbarui'),
        backgroundColor: AppColors.primary,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Gagal: $e'),
        backgroundColor: AppColors.error,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _confirmLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Keluar?', style: GoogleFonts.playfairDisplay(fontWeight: FontWeight.w700)),
        content: const Text('Anda akan keluar dari akun Resepku.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
    if (confirm == true) {
      await ref.read(authNotifierProvider.notifier).signOut();
      if (mounted) context.go(AppRoutes.login);
    }
  }

  @override
  Widget build(BuildContext context) {
    final profileAsync = ref.watch(currentProfileProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: AppColors.textPrimary),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go(widget.isAdmin ? AppRoutes.adminDashboard : AppRoutes.home);
            }
          },
        ),
        title: Text('Profil Saya',
            style: GoogleFonts.playfairDisplay(
                fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
        actions: [
          if (!_isEditing)
            TextButton.icon(
              onPressed: () {
                final name = profileAsync.value?.fullName ?? '';
                _nameController.text = name;
                setState(() => _isEditing = true);
              },
              icon: const Icon(Icons.edit_outlined, size: 16, color: AppColors.primary),
              label: const Text('Edit', style: TextStyle(color: AppColors.primary)),
            ),
        ],
      ),
      body: profileAsync.when(
        data: (profile) {
          if (profile == null) return const Center(child: Text('Profil tidak ditemukan'));
          final initial = profile.displayName.isNotEmpty
              ? profile.displayName.substring(0, 1).toUpperCase()
              : 'U';

          return SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            padding: const EdgeInsets.all(20),
            child: Form(
              key: _formKey,
              child: Column(children: [
                const SizedBox(height: 12),

                // ── Avatar ──────────────────────────────────────────
                CircleAvatar(
                  radius: 52,
                  backgroundColor: AppColors.primaryContainer,
                  child: Text(initial,
                      style: GoogleFonts.playfairDisplay(
                          fontSize: 44, fontWeight: FontWeight.w700, color: AppColors.primary)),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                  decoration: BoxDecoration(
                    color: profile.isAdmin ? AppColors.primary : AppColors.primaryContainer,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    profile.isAdmin ? '👑 ADMIN' : '👤 USER',
                    style: TextStyle(
                      color: profile.isAdmin ? Colors.white : AppColors.primary,
                      fontSize: 12, fontWeight: FontWeight.w700, letterSpacing: 1,
                    ),
                  ),
                ),
                const SizedBox(height: 28),

                // ── Info Card ────────────────────────────────────────
                _Card(children: [
                  _InfoRow(
                    label: 'Nama Lengkap',
                    icon: Icons.person_outline_rounded,
                    child: _isEditing
                        ? TextFormField(
                            controller: _nameController,
                            decoration: const InputDecoration(
                              hintText: 'Masukkan nama lengkap',
                              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                            validator: (v) => (v == null || v.isEmpty) ? 'Nama wajib diisi' : null,
                          )
                        : Text(profile.fullName ?? '-',
                            style: Theme.of(context).textTheme.bodyLarge),
                  ),
                  const Divider(height: 1),
                  _InfoRow(
                    label: 'Email',
                    icon: Icons.email_outlined,
                    child: Text(profile.email, style: Theme.of(context).textTheme.bodyLarge),
                  ),
                  const Divider(height: 1),
                  _InfoRow(
                    label: 'Role',
                    icon: Icons.shield_outlined,
                    child: Text(
                      profile.isAdmin ? 'Administrator' : 'Pengguna Biasa',
                      style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: AppColors.primary),
                    ),
                  ),
                  const Divider(height: 1),
                  _InfoRow(
                    label: 'Bergabung',
                    icon: Icons.calendar_today_outlined,
                    child: Text(
                      '${profile.createdAt.day}/${profile.createdAt.month}/${profile.createdAt.year}',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                  ),
                ]),

                // ── Save / Cancel ──────────────────────────────────────
                if (_isEditing) ...[
                  const SizedBox(height: 16),
                  Row(children: [
                    Expanded(
                      child: OutlinedButton(
                          onPressed: () => setState(() => _isEditing = false),
                          child: const Text('Batal')),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isSaving ? null : _saveProfile,
                        child: _isSaving
                            ? const SizedBox(height: 18, width: 18,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Text('Simpan'),
                      ),
                    ),
                  ]),
                ],

                const SizedBox(height: 20),

                // ── Menu ───────────────────────────────────────────────
                _Card(children: [
                  if (!profile.isAdmin) ...[
                    _MenuRow(
                      icon: Icons.receipt_long_outlined,
                      label: 'Status Resep Saya',
                      color: AppColors.primary,
                      onTap: () => context.push(AppRoutes.myRecipes),
                    ),
                    const Divider(height: 1),
                  ],
                  _MenuRow(
                    icon: Icons.info_outline_rounded,
                    label: 'Tentang Resepku',
                    color: AppColors.textTertiary,
                    onTap: () => showAboutDialog(
                      context: context,
                      applicationName: 'Resepku',
                      applicationVersion: '1.0.0',
                    ),
                  ),
                ]),

                const SizedBox(height: 20),

                // ── Logout ─────────────────────────────────────────────
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _confirmLogout,
                    icon: const Icon(Icons.logout_rounded, color: AppColors.error),
                    label: const Text('Keluar dari Akun',
                        style: TextStyle(color: AppColors.error)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: AppColors.error),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
                const SizedBox(height: 40),
              ]),
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }
}

class _Card extends StatelessWidget {
  final List<Widget> children;
  const _Card({required this.children});
  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    decoration: BoxDecoration(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(16),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8)],
    ),
    child: Column(children: children),
  );
}

class _InfoRow extends StatelessWidget {
  final String label;
  final IconData icon;
  final Widget child;
  const _InfoRow({required this.label, required this.icon, required this.child});
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Icon(icon, size: 20, color: AppColors.primary),
      const SizedBox(width: 12),
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(label, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppColors.textTertiary)),
        const SizedBox(height: 4),
        child,
      ])),
    ]),
  );
}

class _MenuRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _MenuRow({required this.icon, required this.label, required this.color, required this.onTap});
  @override
  Widget build(BuildContext context) => ListTile(
    leading: Icon(icon, color: color),
    title: Text(label, style: Theme.of(context).textTheme.titleMedium),
    trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: AppColors.textDisabled),
    onTap: onTap,
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
  );
}
