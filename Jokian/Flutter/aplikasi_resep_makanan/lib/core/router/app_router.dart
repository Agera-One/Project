// lib/core/router/app_router.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:resepku/features/auth/presentation/providers/auth_provider.dart';
import 'package:resepku/features/auth/presentation/screens/login_screen.dart';
import 'package:resepku/features/auth/presentation/screens/register_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/home_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/recipe_detail_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/recipe_form_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/search_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/saved_screen.dart';
import 'package:resepku/features/recipes/presentation/screens/my_recipes_screen.dart';
import 'package:resepku/features/profile/presentation/screens/profile_screen.dart';
import 'package:resepku/features/admin/presentation/screens/admin_dashboard_screen.dart';
import 'package:resepku/features/admin/presentation/screens/admin_review_screen.dart';
import 'package:resepku/features/admin/presentation/screens/admin_recipes_screen.dart';
import 'package:resepku/shared/widgets/main_scaffold.dart';

class AppRoutes {
  static const String login        = '/login';
  static const String register     = '/register';
  static const String home         = '/';
  static const String search       = '/search';
  static const String saved        = '/saved';
  static const String recipeForm   = '/resep/baru';
  static const String myRecipes    = '/resep/saya';
  static const String profile      = '/profil';
  static const String adminDashboard = '/admin';
  static const String adminReview  = '/admin/review';
  static const String adminRecipes = '/admin/recipes';
  static const String adminProfile = '/admin-profil'; // BUKAN sub /admin agar tidak diblokir
}

// Daftar route yang TIDAK perlu di-redirect meski strukturnya mirip /admin
const _adminOnlyPaths = ['/admin', '/admin/review', '/admin/recipes'];

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authStateProvider);

  return GoRouter(
    initialLocation: AppRoutes.login,
    debugLogDiagnostics: false,

    redirect: (context, state) async {
      final session = authState.value?.session;
      final isLoggedIn = session != null;
      final loc = state.matchedLocation;
      final isAuthRoute = loc == AppRoutes.login || loc == AppRoutes.register;

      // 1. Belum login → paksa ke login
      if (!isLoggedIn && !isAuthRoute) return AppRoutes.login;

      // 2. Sudah login di halaman auth → redirect sesuai role
      if (isLoggedIn && isAuthRoute) {
        try {
          final supabase = ref.read(supabaseProvider);
          final data = await supabase
              .from('profiles')
              .select('role')
              .eq('id', session.user.id)
              .single();
          final role = data['role'] as String? ?? 'user';
          return role == 'admin' ? AppRoutes.adminDashboard : AppRoutes.home;
        } catch (_) {
          return AppRoutes.home;
        }
      }

      // 3. Blokir user biasa HANYA dari path admin murni
      final isAdminPath = _adminOnlyPaths.any((p) => loc == p);
      if (isLoggedIn && isAdminPath) {
        try {
          final supabase = ref.read(supabaseProvider);
          final data = await supabase
              .from('profiles')
              .select('role')
              .eq('id', session.user.id)
              .single();
          final role = data['role'] as String? ?? 'user';
          if (role != 'admin') return AppRoutes.home;
        } catch (_) {
          return AppRoutes.home;
        }
      }

      return null;
    },

    routes: [
      // ── Auth ──────────────────────────────────────────────────────
      GoRoute(path: AppRoutes.login,    name: 'login',    pageBuilder: (c, s) => _page(s, const LoginScreen())),
      GoRoute(path: AppRoutes.register, name: 'register', pageBuilder: (c, s) => _page(s, const RegisterScreen())),

      // ── Full-screen (tanpa bottom nav) ─────────────────────────────
      GoRoute(path: AppRoutes.recipeForm,   name: 'recipeForm',   pageBuilder: (c, s) => _page(s, const RecipeFormScreen())),
      GoRoute(path: AppRoutes.myRecipes,    name: 'myRecipes',    pageBuilder: (c, s) => _page(s, const MyRecipesScreen())),
      GoRoute(path: AppRoutes.profile,      name: 'profile',      pageBuilder: (c, s) => _page(s, const ProfileScreen(isAdmin: false))),
      GoRoute(path: AppRoutes.adminProfile, name: 'adminProfile', pageBuilder: (c, s) => _page(s, const ProfileScreen(isAdmin: true))),
      GoRoute(
        path: '/recipe/:id',
        name: 'recipeDetail',
        pageBuilder: (c, s) => _page(s, RecipeDetailScreen(recipeId: s.pathParameters['id']!)),
      ),

      // ── User Shell ─────────────────────────────────────────────────
      ShellRoute(
        builder: (c, s, child) => MainScaffold(child: child, isAdmin: false),
        routes: [
          GoRoute(path: AppRoutes.home,   name: 'home',   pageBuilder: (c, s) => _page(s, const HomeScreen())),
          GoRoute(path: AppRoutes.search, name: 'search', pageBuilder: (c, s) => _page(s, const SearchScreen())),
          GoRoute(path: AppRoutes.saved,  name: 'saved',  pageBuilder: (c, s) => _page(s, const SavedScreen())),
        ],
      ),

      // ── Admin Shell ────────────────────────────────────────────────
      ShellRoute(
        builder: (c, s, child) => MainScaffold(child: child, isAdmin: true),
        routes: [
          GoRoute(path: AppRoutes.adminDashboard, name: 'adminDashboard', pageBuilder: (c, s) => _page(s, const AdminDashboardScreen())),
          GoRoute(path: AppRoutes.adminReview,    name: 'adminReview',    pageBuilder: (c, s) => _page(s, const AdminReviewScreen())),
          GoRoute(path: AppRoutes.adminRecipes,   name: 'adminRecipes',   pageBuilder: (c, s) => _page(s, const AdminRecipesScreen())),
        ],
      ),
    ],

    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          const Icon(Icons.error_outline, size: 64, color: Colors.red),
          const SizedBox(height: 16),
          Text('Halaman tidak ditemukan', style: Theme.of(context).textTheme.headlineMedium),
          const SizedBox(height: 8),
          ElevatedButton(onPressed: () => context.go(AppRoutes.home), child: const Text('Kembali ke Beranda')),
        ]),
      ),
    ),
  );
});

CustomTransitionPage _page(GoRouterState s, Widget child) => CustomTransitionPage(
  key: s.pageKey,
  child: child,
  transitionsBuilder: (ctx, anim, _, child) =>
      FadeTransition(opacity: CurveTween(curve: Curves.easeInOut).animate(anim), child: child),
  transitionDuration: const Duration(milliseconds: 200),
);
