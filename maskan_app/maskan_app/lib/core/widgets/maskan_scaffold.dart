import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// الغلاف الأساسي لشاشات مسكن — خلفية متدرجة مع دوائر زخرفية ومعالجة اختيارية للمنطقة الآمنة
class MaskanScaffold extends StatelessWidget {
  /// The main body content of the screen.
  final Widget body;

  /// Optional app bar displayed at the top.
  final PreferredSizeWidget? appBar;

  /// Optional bottom navigation bar.
  final Widget? bottomNavigationBar;

  /// Optional floating action button.
  final Widget? floatingActionButton;

  /// If true, the body will not be wrapped in a [SafeArea].
  final bool noSafeArea;

  const MaskanScaffold({
    super.key,
    required this.body,
    this.appBar,
    this.bottomNavigationBar,
    this.floatingActionButton,
    this.noSafeArea = false,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      backgroundColor: Colors.transparent,
      extendBody: true,
      extendBodyBehindAppBar: true,
      appBar: appBar,
      bottomNavigationBar: bottomNavigationBar,
      floatingActionButton: floatingActionButton,
      body: Stack(
        children: [
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
                colors: isDark
                    ? [const Color(0xFF0D2A4A), const Color(0xFF061525), const Color(0xFF020B14)]
                    : [const Color(0xFFDEEEF9), const Color(0xFFE8F3FB), const Color(0xFFF5F0E8)],
                stops: const [0.0, 0.5, 1.0],
              ),
            ),
          ),
          Positioned(
            top: -40, right: -40,
            child: _Orb(size: 220, color: isDark ? AppColors.orbBlueDark : AppColors.orbBlueLight),
          ),
          Positioned(
            bottom: 60, left: -30,
            child: _Orb(size: 160, color: isDark ? AppColors.orbGoldDark : AppColors.orbGoldLight),
          ),
          if (noSafeArea) body else SafeArea(child: body),
        ],
      ),
    );
  }
}

/// عنصر خلفية فقط بنفس التدرج والدوائر — للتخطيطات المخصصة التي تحتاج المظهر بدون الهيكل الكامل
class MaskanBackground extends StatelessWidget {
  /// The content to display on top of the background.
  final Widget child;

  /// If true, the child will not be wrapped in a [SafeArea].
  final bool noSafeArea;

  const MaskanBackground({super.key, required this.child, this.noSafeArea = false});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Stack(
      children: [
        Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topRight,
              end: Alignment.bottomLeft,
              colors: isDark
                  ? [const Color(0xFF0D2A4A), const Color(0xFF061525), const Color(0xFF020B14)]
                  : [const Color(0xFFDEEEF9), const Color(0xFFE8F3FB), const Color(0xFFF5F0E8)],
              stops: const [0.0, 0.5, 1.0],
            ),
          ),
        ),
        Positioned(
          top: -40, right: -40,
          child: _Orb(size: 220, color: isDark ? AppColors.orbBlueDark : AppColors.orbBlueLight),
        ),
        Positioned(
          bottom: 60, left: -30,
          child: _Orb(size: 160, color: isDark ? AppColors.orbGoldDark : AppColors.orbGoldLight),
        ),
        if (noSafeArea) child else SafeArea(child: child),
      ],
    );
  }
}

/// دائرة زخرفية متدرجة تستخدم في خلفية [MaskanScaffold]
class _Orb extends StatelessWidget {
  final double size;
  final Color color;
  const _Orb({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(
          colors: [color, Colors.transparent],
          stops: const [0.0, 1.0],
        ),
      ),
    );
  }
}
