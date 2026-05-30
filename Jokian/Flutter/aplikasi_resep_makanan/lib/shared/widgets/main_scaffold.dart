// lib/shared/widgets/main_scaffold.dart

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:resepku/core/router/app_router.dart';
import 'package:resepku/core/theme/app_theme.dart';

class MainScaffold extends StatelessWidget {
  final Widget child;
  final bool isAdmin;

  const MainScaffold({super.key, required this.child, this.isAdmin = false});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: _BottomNav(isAdmin: isAdmin),
    );
  }
}

class _BottomNav extends StatelessWidget {
  final bool isAdmin;
  const _BottomNav({this.isAdmin = false});

  int _getIndex(BuildContext context) {
    final loc = GoRouterState.of(context).matchedLocation;
    if (isAdmin) {
      if (loc == AppRoutes.adminDashboard) return 0;
      if (loc == AppRoutes.adminReview) return 1;
      if (loc == AppRoutes.adminRecipes) return 2;
      return 0;
    }
    if (loc == AppRoutes.home) return 0;
    if (loc.startsWith('/search')) return 1;
    if (loc == AppRoutes.saved) return 2;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final idx = _getIndex(context);

    // User: Home | Search | Saved
    const userItems = [
      BottomNavigationBarItem(
        icon: Icon(Icons.home_outlined),
        activeIcon: Icon(Icons.home_rounded),
        label: 'Home',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.search_outlined),
        activeIcon: Icon(Icons.search_rounded),
        label: 'Search',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.bookmark_outline),
        activeIcon: Icon(Icons.bookmark_rounded),
        label: 'Saved',
      ),
    ];

    // Admin: Dashboard | Review | Resep
    const adminItems = [
      BottomNavigationBarItem(
        icon: Icon(Icons.analytics_outlined),
        activeIcon: Icon(Icons.analytics_rounded),
        label: 'Dashboard',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.pending_outlined),
        activeIcon: Icon(Icons.pending_rounded),
        label: 'Review',
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.restaurant_menu_outlined),
        activeIcon: Icon(Icons.restaurant_menu_rounded),
        label: 'Resep',
      ),
    ];

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: BottomNavigationBar(
        currentIndex: idx,
        items: isAdmin ? adminItems : userItems,
        onTap: (i) {
          if (isAdmin) {
            switch (i) {
              case 0: context.go(AppRoutes.adminDashboard); break;
              case 1: context.go(AppRoutes.adminReview); break;
              case 2: context.go(AppRoutes.adminRecipes); break;
            }
          } else {
            switch (i) {
              case 0: context.go(AppRoutes.home); break;
              case 1: context.go(AppRoutes.search); break;
              case 2: context.go(AppRoutes.saved); break;
            }
          }
        },
      ),
    );
  }
}
