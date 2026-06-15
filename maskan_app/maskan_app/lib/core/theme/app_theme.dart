import 'package:flutter/material.dart';
import 'app_colors.dart';

/// إعدادات الثيم (السمة) العامة للتطبيق.
/// توفر ثيماً جاهزاً للوضع النهاري والليلي باستخدام خط Cairo.
class AppTheme {
  AppTheme._();

  /// يُنشئ ثيم الوضع الليلي (الداكن) باستخدام ألوان [AppColors] المخصصة.
  static ThemeData dark() => _themeData(
        brightness: Brightness.dark,
        bg: AppColors.darkBg,
        primary: AppColors.blue,
        secondary: AppColors.gold,
        surface: AppColors.darkSurface,
        textPrimary: AppColors.darkTextPrimary,
        textSecondary: AppColors.darkTextSecondary,
        inputBg: const Color(0x26FFFFFF),
        border: AppColors.darkBorder,
        enabledBorder: AppColors.darkBorder,
      );

  /// يُنشئ ثيم الوضع النهاري (الفاتح) باستخدام ألوان [AppColors] المخصصة.
  static ThemeData light() => _themeData(
        brightness: Brightness.light,
        bg: AppColors.lightBg,
        primary: AppColors.blue,
        secondary: AppColors.gold,
        surface: Colors.white,
        textPrimary: AppColors.lightTextPrimary,
        textSecondary: AppColors.lightTextSecondary,
        inputBg: const Color(0xB0FFFFFF),
        border: AppColors.lightBorder,
        enabledBorder: AppColors.lightBorderSub,
      );

  /// يُنشئ ثيم [ThemeData] مخصص باستخدام المعاملات المُمررة
  static ThemeData _themeData({
    required Brightness brightness,
    required Color bg,
    required Color primary,
    required Color secondary,
    required Color surface,
    required Color textPrimary,
    required Color textSecondary,
    required Color inputBg,
    required Color border,
    required Color enabledBorder,
  }) {
    return ThemeData(
      useMaterial3: true,
      brightness: brightness,
      scaffoldBackgroundColor: bg,
      fontFamily: 'Cairo',
      colorScheme: ColorScheme(
        brightness: brightness,
        primary: primary,
        secondary: secondary,
        surface: surface,
        error: AppColors.danger,
        onPrimary: Colors.white,
        onSecondary: Colors.white,
        onSurface: textPrimary,
        onError: Colors.white,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        foregroundColor: textPrimary,
        titleTextStyle: TextStyle(
          fontFamily: 'Cairo',
          fontSize: 17,
          fontWeight: FontWeight.w700,
          color: textPrimary,
        ),
      ),
      textTheme: TextTheme(
        headlineLarge: TextStyle(fontFamily: 'Cairo', fontSize: 26, fontWeight: FontWeight.w700, color: textPrimary),
        headlineMedium: TextStyle(fontFamily: 'Cairo', fontSize: 22, fontWeight: FontWeight.w600, color: textPrimary),
        headlineSmall: TextStyle(fontFamily: 'Cairo', fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
        bodyLarge: TextStyle(fontFamily: 'Cairo', fontSize: 15, color: textSecondary),
        bodyMedium: TextStyle(fontFamily: 'Cairo', fontSize: 13, color: textSecondary),
        labelMedium: TextStyle(fontFamily: 'Cairo', fontSize: 12, fontWeight: FontWeight.w500, color: textSecondary),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: inputBg,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: border, width: 0.8),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: enabledBorder, width: 0.8),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: primary, width: 1.5),
        ),
        hintStyle: TextStyle(fontFamily: 'Cairo', fontSize: 14, color: textSecondary),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          minimumSize: const Size(double.infinity, 52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          textStyle: const TextStyle(fontFamily: 'Cairo', fontSize: 15, fontWeight: FontWeight.w700),
          elevation: 0,
        ),
      ),
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: Colors.transparent,
        elevation: 0,
        type: BottomNavigationBarType.fixed,
        selectedItemColor: primary,
        unselectedItemColor: textSecondary,
      ),
      dividerTheme: DividerThemeData(
        color: border,
        thickness: 0.5,
        space: 0,
      ),
    );
  }
}
