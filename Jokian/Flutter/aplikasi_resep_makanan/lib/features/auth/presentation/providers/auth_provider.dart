// lib/features/auth/presentation/providers/auth_provider.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:resepku/features/auth/data/models/profile_model.dart';
import 'package:resepku/core/constants/app_constants.dart';

// ── Supabase client ──────────────────────────────────────────────────────────
final supabaseProvider = Provider<SupabaseClient>((ref) {
  return Supabase.instance.client;
});

// ── Auth State Stream ────────────────────────────────────────────────────────
final authStateProvider = StreamProvider<AuthState>((ref) {
  return ref.watch(supabaseProvider).auth.onAuthStateChange;
});

// ── Current User (DIPERBAIKI: Memantau Stream secara Real-time) ──────────────
final currentUserProvider = Provider<User?>((ref) {
  final authStateAsync = ref.watch(authStateProvider);
  
  return authStateAsync.maybeWhen(
    data: (state) => state.session?.user, // <-- Perbaikan di baris ini
    orElse: () => ref.read(supabaseProvider).auth.currentUser,
  );
});

// ── Current Profile ──────────────────────────────────────────────────────────
final currentProfileProvider = FutureProvider<ProfileModel?>((ref) async {
  final user = ref.watch(currentUserProvider);
  if (user == null) return null;

  final supabase = ref.watch(supabaseProvider);
  try {
    final response = await supabase
        .from(AppConstants.profilesTable)
        .select()
        .eq('id', user.id)
        .single();
    return ProfileModel.fromMap(response);
  } catch (e) {
    return null;
  }
});

// ── Is Admin ──────────────────────────────────────────────────────────────────
final isAdminProvider = FutureProvider<bool>((ref) async {
  final profile = await ref.watch(currentProfileProvider.future);
  return profile?.isAdmin ?? false;
});

// ── Auth Notifier (DIPERBAIKI: Sinkronisasi Invalidasi State) ─────────────────
class AuthNotifier extends AsyncNotifier<ProfileModel?> {
  @override
  Future<ProfileModel?> build() async {
    final user = ref.watch(currentUserProvider);
    if (user == null) return null;
    return ref.watch(currentProfileProvider.future);
  }

  // ── Sign In ────────────────────────────────────────────
  Future<AuthResponse> signIn({
    required String email,
    required String password,
  }) async {
    final supabase = ref.read(supabaseProvider);
    final response = await supabase.auth.signInWithPassword(
      email: email,
      password: password,
    );
    
    // Paksa Riverpod mereset data profil lama agar langsung memuat profil baru
    ref.invalidate(currentUserProvider);
    ref.invalidate(currentProfileProvider);
    ref.invalidateSelf();
    return response;
  }

  // ── Sign Up ────────────────────────────────────────────
  Future<AuthResponse> signUp({
    required String email,
    required String password,
    required String fullName,
  }) async {
    final supabase = ref.read(supabaseProvider);
    final response = await supabase.auth.signUp(
      email: email,
      password: password,
      data: {'full_name': fullName},
    );
    
    ref.invalidate(currentUserProvider);
    ref.invalidate(currentProfileProvider);
    ref.invalidateSelf();
    return response;
  }

  // ── Sign Out ───────────────────────────────────────────
  Future<void> signOut() async {
    final supabase = ref.read(supabaseProvider);
    await supabase.auth.signOut();
    
    // Bersihkan semua data cache user dari memory aplikasi saat logout
    ref.invalidate(currentUserProvider);
    ref.invalidate(currentProfileProvider);
    ref.invalidateSelf();
  }

  // ── Update Profile ─────────────────────────────────────
  Future<void> updateProfile({String? fullName}) async {
    final user = ref.read(currentUserProvider);
    if (user == null) return;

    final supabase = ref.read(supabaseProvider);
    await supabase
        .from(AppConstants.profilesTable)
        .update({'full_name': fullName})
        .eq('id', user.id);

    ref.invalidate(currentProfileProvider);
    ref.invalidateSelf();
  }
}

final authNotifierProvider = AsyncNotifierProvider<AuthNotifier, ProfileModel?>(
  AuthNotifier.new,
);