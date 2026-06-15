import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../l10n/app_localizations.dart';
import 'maskan_curved_nav.dart';

/// غلاف الشاشات بشريط تنقل سفلي مناسب لدور المستخدم — تحديد التبويب النشط حسب المسار الحالي
class RouteShell extends StatelessWidget {
  /// The screen content to display above the bottom navigation bar.
  final Widget child;
  const RouteShell({super.key, required this.child});

  /// Maps a route location string to a bottom-nav tab index.
  int _indexForLocation(String loc) {
    if (loc.startsWith(AppRoutes.propertyList) || loc.startsWith(AppRoutes.favorites)) { return 1; }
    if (loc.startsWith(AppRoutes.bookings) || loc.startsWith(AppRoutes.bookingForm.replaceAll(':propertyId', '')) || loc.startsWith(AppRoutes.payment.replaceAll(':bookingId', '')) || loc.startsWith(AppRoutes.bookingDetail.replaceAll(':bookingId', ''))) { return 2; }
    if (loc.startsWith(AppRoutes.profile) || loc.startsWith(AppRoutes.editProfile) || loc.startsWith(AppRoutes.settings) || loc.startsWith(AppRoutes.maintenanceRequests) || loc.startsWith(AppRoutes.complaintForm) || loc.startsWith(AppRoutes.technicianReviews)) { return 3; }
    if (loc.startsWith(AppRoutes.taskDetail.replaceAll(':id', ''))) { return 2; }
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final loc = AppLocalizations.of(context);

    List<MaskanCurvedNavItem> items;
    int currentIndex;
    void Function(int) onTap;

    if (!auth.isLoggedIn) {
      items = [
        MaskanCurvedNavItem(icon: Icons.home_rounded, label: loc.home),
        MaskanCurvedNavItem(icon: Icons.search_rounded, label: loc.search),
      ];
      currentIndex = _indexForLocation(GoRouterState.of(context).matchedLocation);
      if (currentIndex > 1) { currentIndex = 0; }
      onTap = (i) {
        if (i == 0) { context.go(AppRoutes.visitorHome); } else { context.go(AppRoutes.propertyList); }
      };
    } else if (auth.isTechnician) {
      items = [
        MaskanCurvedNavItem(icon: Icons.assignment_rounded, label: loc.tasks),
        MaskanCurvedNavItem(icon: Icons.person_rounded, label: loc.profile),
      ];
      currentIndex = _indexForLocation(GoRouterState.of(context).matchedLocation);
      if (currentIndex > 1) { currentIndex = 0; }
      onTap = (i) {
        if (i == 0) { context.go(AppRoutes.technicianHome); } else { context.go(AppRoutes.profile); }
      };
    } else {
      items = [
        MaskanCurvedNavItem(icon: Icons.home_rounded, label: loc.home),
        MaskanCurvedNavItem(icon: Icons.search_rounded, label: loc.search),
        MaskanCurvedNavItem(icon: Icons.calendar_today_rounded, label: loc.bookings),
        MaskanCurvedNavItem(icon: Icons.person_rounded, label: loc.account),
      ];
      currentIndex = _indexForLocation(GoRouterState.of(context).matchedLocation);
      onTap = (i) {
        switch (i) {
          case 0: context.go(AppRoutes.tenantHome);
          case 1: context.go(AppRoutes.propertyList);
          case 2: context.go(AppRoutes.bookings);
          case 3: context.go(AppRoutes.profile);
        }
      };
    }

    return Scaffold(
      body: child,
      bottomNavigationBar: MaskanCurvedNav(
        currentIndex: currentIndex,
        items: items,
        onTap: onTap,
      ),
    );
  }
}
