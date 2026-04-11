// lib/screens/auth_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/auth_provider.dart';
import '../theme.dart';

class AuthScreen extends ConsumerStatefulWidget {
  const AuthScreen({super.key});

  @override
  ConsumerState<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends ConsumerState<AuthScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();

  bool _obscurePass = true;
  bool _obscureConfirm = true;

  late final AnimationController _animCtrl;
  late final Animation<double> _fadeAnim;
  late final Animation<Offset> _slideAnim;

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut));
    _animCtrl.forward();
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  // ── Toggle mode (login ↔ register) ───────────────────────
  void _switchMode() {
    _formKey.currentState?.reset();
    _nameCtrl.clear();
    _emailCtrl.clear();
    _passCtrl.clear();
    _confirmPassCtrl.clear();
    ref.read(authNotifierProvider.notifier).toggleMode();
    _animCtrl.forward(from: 0);
  }

  // ── Submit form ───────────────────────────────────────────
  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    FocusScope.of(context).unfocus();

    final notifier = ref.read(authNotifierProvider.notifier);
    final isSignUp = ref.read(authNotifierProvider).isSignUp;

    if (isSignUp) {
      await notifier.signUp(
        fullName: _nameCtrl.text,
        email: _emailCtrl.text,
        password: _passCtrl.text,
      );
    } else {
      await notifier.signIn(
        email: _emailCtrl.text,
        password: _passCtrl.text,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final isSignUp = authState.isSignUp;
    final isLoading = authState.isLoading;

    // Tampilkan error sebagai SnackBar
    ref.listen(authNotifierProvider, (prev, next) {
      if (next.errorMessage != null &&
          next.errorMessage != prev?.errorMessage) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline_rounded,
                    color: Colors.white, size: 18),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    next.errorMessage!,
                    style: GoogleFonts.poppins(
                        fontSize: 13, color: Colors.white),
                  ),
                ),
              ],
            ),
            backgroundColor: kPdfColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10)),
            margin: const EdgeInsets.all(16),
            duration: const Duration(seconds: 4),
          ),
        );
        ref.read(authNotifierProvider.notifier).clearError();
      }
    });

    return Scaffold(
      backgroundColor: kBgColor,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            children: [
              const SizedBox(height: 48),

              // ── Logo & Branding ──────────────────────────
              _buildLogo(),

              const SizedBox(height: 40),

              // ── Card Form ────────────────────────────────
              FadeTransition(
                opacity: _fadeAnim,
                child: SlideTransition(
                  position: _slideAnim,
                  child: _buildCard(isSignUp, isLoading),
                ),
              ),

              const SizedBox(height: 24),

              // ── Toggle Login/Register ────────────────────
              _buildToggle(isSignUp, isLoading),

              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  // ── Logo ──────────────────────────────────────────────────
  Widget _buildLogo() {
    return Column(
      children: [
        Container(
          width: 72,
          height: 72,
          decoration: BoxDecoration(
            color: kAccentGreen.withOpacity(0.12),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: kAccentGreen.withOpacity(0.25),
              width: 1.5,
            ),
          ),
          child: const Icon(
            Icons.folder_special_rounded,
            color: kAccentGreen,
            size: 36,
          ),
        ),
        const SizedBox(height: 16),
        Text(
          'Document Archiver',
          style: GoogleFonts.poppins(
            color: kTextPrimary,
            fontSize: 22,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.3,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Kelola dokumenmu dengan mudah',
          style: GoogleFonts.poppins(
            color: kTextTertiary,
            fontSize: 13,
          ),
        ),
      ],
    );
  }

  // ── Form Card ─────────────────────────────────────────────
  Widget _buildCard(bool isSignUp, bool isLoading) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: kSurfaceColor,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: kDividerColor),
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title
            Text(
              isSignUp ? 'Buat Akun Baru' : 'Selamat Datang',
              style: GoogleFonts.poppins(
                color: kTextPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
            Text(
              isSignUp
                  ? 'Daftar untuk mulai mengarsipkan dokumen'
                  : 'Masuk ke akun kamu',
              style: GoogleFonts.poppins(
                color: kTextTertiary,
                fontSize: 12,
              ),
            ),

            const SizedBox(height: 24),

            // Full Name (hanya saat register)
            if (isSignUp) ...[
              _buildLabel('Nama Lengkap'),
              const SizedBox(height: 6),
              _buildTextField(
                controller: _nameCtrl,
                hint: 'Contoh: Dzaki Prasetyo',
                icon: Icons.person_outline_rounded,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) {
                    return 'Nama tidak boleh kosong';
                  }
                  if (v.trim().length < 2) {
                    return 'Nama terlalu pendek';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
            ],

            // Email
            _buildLabel('Email'),
            const SizedBox(height: 6),
            _buildTextField(
              controller: _emailCtrl,
              hint: 'email@contoh.com',
              icon: Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.trim().isEmpty) {
                  return 'Email tidak boleh kosong';
                }
                if (!RegExp(r'^[\w.-]+@[\w.-]+\.\w+$').hasMatch(v.trim())) {
                  return 'Format email tidak valid';
                }
                return null;
              },
            ),

            const SizedBox(height: 16),

            // Password
            _buildLabel('Password'),
            const SizedBox(height: 6),
            _buildTextField(
              controller: _passCtrl,
              hint: isSignUp ? 'Minimal 6 karakter' : 'Masukkan password',
              icon: Icons.lock_outline_rounded,
              obscure: _obscurePass,
              suffixIcon: IconButton(
                icon: Icon(
                  _obscurePass
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: kTextTertiary,
                  size: 20,
                ),
                onPressed: () =>
                    setState(() => _obscurePass = !_obscurePass),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) {
                  return 'Password tidak boleh kosong';
                }
                if (isSignUp && v.length < 6) {
                  return 'Password minimal 6 karakter';
                }
                return null;
              },
            ),

            // Confirm Password (hanya saat register)
            if (isSignUp) ...[
              const SizedBox(height: 16),
              _buildLabel('Konfirmasi Password'),
              const SizedBox(height: 6),
              _buildTextField(
                controller: _confirmPassCtrl,
                hint: 'Ulangi password kamu',
                icon: Icons.lock_outline_rounded,
                obscure: _obscureConfirm,
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureConfirm
                        ? Icons.visibility_outlined
                        : Icons.visibility_off_outlined,
                    color: kTextTertiary,
                    size: 20,
                  ),
                  onPressed: () =>
                      setState(() => _obscureConfirm = !_obscureConfirm),
                ),
                validator: (v) {
                  if (v == null || v.isEmpty) {
                    return 'Konfirmasi password tidak boleh kosong';
                  }
                  if (v != _passCtrl.text) {
                    return 'Password tidak cocok';
                  }
                  return null;
                },
              ),
            ],

            const SizedBox(height: 28),

            // Submit Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: isLoading ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: kAccentGreen,
                  disabledBackgroundColor: kAccentGreen.withOpacity(0.4),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: isLoading
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          color: Colors.black,
                          strokeWidth: 2.5,
                        ),
                      )
                    : Text(
                        isSignUp ? 'Daftar Sekarang' : 'Masuk',
                        style: GoogleFonts.poppins(
                          color: Colors.black,
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Label helper ──────────────────────────────────────────
  Widget _buildLabel(String text) {
    return Text(
      text,
      style: GoogleFonts.poppins(
        color: kTextSecondary,
        fontSize: 12,
        fontWeight: FontWeight.w500,
      ),
    );
  }

  // ── TextField helper ──────────────────────────────────────
  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    bool obscure = false,
    Widget? suffixIcon,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscure,
      keyboardType: keyboardType,
      style: GoogleFonts.poppins(color: kTextPrimary, fontSize: 14),
      validator: validator,
      autovalidateMode: AutovalidateMode.onUserInteraction,
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.poppins(color: kTextTertiary, fontSize: 13),
        prefixIcon: Icon(icon, color: kTextTertiary, size: 20),
        suffixIcon: suffixIcon,
        filled: true,
        fillColor: kSurface2Color,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kDividerColor, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:
              const BorderSide(color: kAccentGreen, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPdfColor, width: 1),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: kPdfColor, width: 1.5),
        ),
        errorStyle: GoogleFonts.poppins(
          color: kPdfColor,
          fontSize: 11,
        ),
      ),
    );
  }

  // ── Toggle Login/Register ─────────────────────────────────
  Widget _buildToggle(bool isSignUp, bool isLoading) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          isSignUp ? 'Sudah punya akun?' : 'Belum punya akun?',
          style: GoogleFonts.poppins(
            color: kTextTertiary,
            fontSize: 13,
          ),
        ),
        const SizedBox(width: 4),
        GestureDetector(
          onTap: isLoading ? null : _switchMode,
          child: Text(
            isSignUp ? 'Masuk' : 'Daftar Sekarang',
            style: GoogleFonts.poppins(
              color: kAccentGreen,
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}
