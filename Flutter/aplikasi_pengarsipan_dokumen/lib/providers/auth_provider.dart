// lib/providers/auth_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

// ── Supabase client shortcut ──────────────────────────────────
SupabaseClient get _auth => Supabase.instance.client;

// ── Stream: memantau perubahan auth state secara real-time ────
final authStateProvider = StreamProvider<AuthState>((ref) {
  return _auth.auth.onAuthStateChange;
});

// ── Convenience: user saat ini (nullable) ─────────────────────
final currentUserProvider = Provider<User?>((ref) {
  final authState = ref.watch(authStateProvider);
  return authState.whenData((state) => state.session?.user).value;
});

// ── Auth state untuk UI (loading, error, dll) ─────────────────
class AuthNotifierState {
  final bool isLoading;
  final String? errorMessage;
  final bool isSignUp; // toggle login/register

  const AuthNotifierState({
    this.isLoading = false,
    this.errorMessage,
    this.isSignUp = false,
  });

  AuthNotifierState copyWith({
    bool? isLoading,
    String? errorMessage,
    bool clearError = false,
    bool? isSignUp,
  }) {
    return AuthNotifierState(
      isLoading: isLoading ?? this.isLoading,
      errorMessage: clearError ? null : errorMessage ?? this.errorMessage,
      isSignUp: isSignUp ?? this.isSignUp,
    );
  }
}

// ── AuthNotifier ──────────────────────────────────────────────
class AuthNotifier extends StateNotifier<AuthNotifierState> {
  AuthNotifier() : super(const AuthNotifierState());

  // Toggle antara mode Login dan Register
  void toggleMode() {
    state = state.copyWith(
      isSignUp: !state.isSignUp,
      clearError: true,
    );
  }

  void clearError() {
    state = state.copyWith(clearError: true);
  }

  // ── Sign In ───────────────────────────────────────────────
  Future<void> signIn({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _auth.auth.signInWithPassword(
        email: email.trim(),
        password: password,
      );
      state = state.copyWith(isLoading: false);
    } on AuthException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: _friendlyError(e.message),
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Terjadi kesalahan. Coba lagi.',
      );
    }
  }

  // ── Sign Up ───────────────────────────────────────────────
  Future<void> signUp({
    required String fullName,
    required String email,
    required String password,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final initials = _buildInitials(fullName);
      await _auth.auth.signUp(
        email: email.trim(),
        password: password,
        data: {
          'full_name': fullName.trim(),
          'initials': initials,
        },
      );
      state = state.copyWith(isLoading: false);
    } on AuthException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: _friendlyError(e.message),
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Terjadi kesalahan. Coba lagi.',
      );
    }
  }

  // ── Sign Out ──────────────────────────────────────────────
  Future<void> signOut() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      await _auth.auth.signOut();
      state = const AuthNotifierState();
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Gagal logout. Coba lagi.',
      );
    }
  }

  // ── Helpers ───────────────────────────────────────────────
  String _buildInitials(String name) {
    final parts = name.trim().split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name.substring(0, name.length >= 2 ? 2 : 1).toUpperCase();
  }

  String _friendlyError(String message) {
    final msg = message.toLowerCase();
    if (msg.contains('invalid login credentials') ||
        msg.contains('invalid credentials')) {
      return 'Email atau password salah.';
    }
    if (msg.contains('email not confirmed')) {
      return 'Email belum dikonfirmasi. Cek inbox kamu.';
    }
    if (msg.contains('user already registered')) {
      return 'Email sudah terdaftar. Silakan login.';
    }
    if (msg.contains('password should be at least')) {
      return 'Password minimal 6 karakter.';
    }
    if (msg.contains('unable to validate email address')) {
      return 'Format email tidak valid.';
    }
    if (msg.contains('network') || msg.contains('connection')) {
      return 'Koneksi bermasalah. Periksa internet kamu.';
    }
    return message;
  }
}

// ── Provider ──────────────────────────────────────────────────
final authNotifierProvider =
    StateNotifierProvider<AuthNotifier, AuthNotifierState>(
  (ref) => AuthNotifier(),
);
