import 'package:flutter/material.dart';
import 'package:curved_navigation_bar/curved_navigation_bar.dart';
import '../../config/colors.dart';

/// عنصر واحد في شريط التنقل المنحني
class MaskanCurvedNavItem {
  /// الأيقونة المعروضة لعنصر التنقل.
  final IconData icon;

  /// النص المعروض لعنصر التنقل.
  final String label;
  const MaskanCurvedNavItem({required this.icon, required this.label});
}

/// شريط تنقل سفلي منحني يلتف حول [CurvedNavigationBar] مع دعم الثيم الداكن/الفاتح
class MaskanCurvedNav extends StatelessWidget {
  /// فهرس التبويب المحدد حالياً.
  final int currentIndex;

  /// قائمة عناصر التنقل المعروضة.
  final List<MaskanCurvedNavItem> items;

  /// يُستدعى عند النقر على تبويب، مع توفير الفهرس الجديد.
  final ValueChanged<int> onTap;

  const MaskanCurvedNav({
    super.key,
    required this.currentIndex,
    required this.items,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return CurvedNavigationBar(
      key: ValueKey(currentIndex),
      index: currentIndex,
      color: isDark ? MaskanColors.kBgCard : Colors.white,
      buttonBackgroundColor: MaskanColors.kGoldDark,
      backgroundColor: Colors.transparent,
      animationCurve: Curves.easeOutCubic,
      animationDuration: const Duration(milliseconds: 600),
      height: 65.0,
      items: items.map((item) {
        return Icon(
          item.icon,
          size: 26,
          color: isDark ? MaskanColors.kBlueSky : MaskanColors.kBlue,
        );
      }).toList(),
      onTap: onTap,
    );
  }
}
