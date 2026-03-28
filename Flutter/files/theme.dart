// lib/theme.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// ─── Color Palette ────────────────────────────────────────────────────────────
const kBgColor = Color(0xFF121212);
const kSurfaceColor = Color(0xFF1E1E1E);
const kSurface2Color = Color(0xFF252525);
const kCardColor = Color(0xFF1A1A1A);
const kDividerColor = Color(0xFF2A2A2A);

const kAccentGreen = Color(0xFF00FF9D);
const kAccentGreenDark = Color(0xFF00C853);
const kTextPrimary = Color(0xFFFFFFFF);
const kTextSecondary = Color(0xFF9E9E9E);
const kTextTertiary = Color(0xFF616161);

// Badge colors
const kPngColor = Color(0xFF00BCD4);
const kPdfColor = Color(0xFFE53935);
const kDocxColor = Color(0xFF1E88E5);
const kPptxColor = Color(0xFFFB8C00);
const kXlsxColor = Color(0xFF43A047);
const kJpgColor = Color(0xFF8E24AA);
const kTxtColor = Color(0xFF78909C);

// Status badge colors
const kStatusActiveColor = Color(0xFF00C853);
const kStatusArchivedColor = Color(0xFFFFB300);
const kStatusDeletedColor = Color(0xFFE53935);

// Owner avatar
const kOwnerAvatarColor = Color(0xFF1F3A5F);

class AppTheme {
  static ThemeData get dark {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: kBgColor,
      colorScheme: const ColorScheme.dark(
        primary: kAccentGreen,
        secondary: kAccentGreenDark,
        surface: kSurfaceColor,
        onPrimary: Colors.black,
        onSecondary: Colors.black,
        onSurface: kTextPrimary,
      ),
      textTheme: GoogleFonts.poppinsTextTheme(ThemeData.dark().textTheme),
      appBarTheme: AppBarTheme(
        backgroundColor: kSurfaceColor,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.poppins(
          color: kTextPrimary,
          fontSize: 18,
          fontWeight: FontWeight.w600,
        ),
        iconTheme: const IconThemeData(color: kTextPrimary),
      ),
      drawerTheme: const DrawerThemeData(
        backgroundColor: kSurfaceColor,
        elevation: 8,
      ),
      cardTheme: const CardTheme(
        color: kSurface2Color,
        elevation: 0,
        margin: EdgeInsets.zero,
      ),
      dividerTheme: const DividerThemeData(
        color: kDividerColor,
        thickness: 1,
        space: 0,
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: kAccentGreen,
        foregroundColor: Colors.black,
        elevation: 4,
      ),
      iconTheme: const IconThemeData(color: kTextSecondary),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: kSurface2Color,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        hintStyle: const TextStyle(color: kTextTertiary),
        prefixIconColor: kTextTertiary,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }
}
