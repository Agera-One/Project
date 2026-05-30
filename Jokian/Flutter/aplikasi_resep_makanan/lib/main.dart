// lib/main.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:resepku/core/constants/app_constants.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/core/theme/app_theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Supabase
  await Supabase.initialize(
    url: AppConstants.supabaseUrl,
    anonKey: AppConstants.supabaseAnonKey,
    debug: false,
  );

  runApp(
    // Riverpod ProviderScope wraps the entire app
    const ProviderScope(
      child: ResepkuApp(),
    ),
  );
}

class ResepkuApp extends ConsumerWidget {
  const ResepkuApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: AppConstants.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,

      // GoRouter integration
      routerConfig: router,

      // Builder for responsive max-width container
      builder: (context, child) {
        return _ResponsiveWrapper(child: child!);
      },
    );
  }
}

/// Wraps the app in a centered, max-width container for web/desktop.
/// Mobile feels native; desktop shows a centered phone-width view.
class _ResponsiveWrapper extends StatelessWidget {
  final Widget child;
  const _ResponsiveWrapper({required this.child});

  static const double _maxMobileWidth = 430;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;

    // On desktop/tablet: center with max width + subtle background
    if (width > _maxMobileWidth) {
      return Scaffold(
        backgroundColor: const Color(0xFFEEE8E0),
        body: Center(
          child: Container(
            width: _maxMobileWidth,
            clipBehavior: Clip.hardEdge,
            decoration: BoxDecoration(
              color: AppColors.background,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.15),
                  blurRadius: 40,
                  spreadRadius: 5,
                ),
              ],
            ),
            child: child,
          ),
        ),
      );
    }

    return child;
  }
}
