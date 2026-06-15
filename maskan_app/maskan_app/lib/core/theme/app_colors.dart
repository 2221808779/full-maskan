import 'package:flutter/material.dart';

/// الألوان المستخدمة في التطبيق بالكامل.
/// تحتوي على ألوان الوضع النهاري والليلي، بالإضافة إلى ألوان العلامة التجارية.
class AppColors {
  AppColors._();

  /// الألوان الزرقاء الرئيسية للعلامة التجارية.
  // ─── Brand Blues ───────────────────────────────
  static const blueDark   = Color(0xFF1B3A52);
  static const blue       = Color(0xFF2D5F8A);
  static const blueLight  = Color(0xFF4A8DBF);
  static const blueSky    = Color(0xFF6BB8E8);

  /// الألوان الذهبية الرئيسية للعلامة التجارية.
  // ─── Brand Golds ───────────────────────────────
  static const goldDark   = Color(0xFFA3700E);
  static const gold       = Color(0xFFC49A2B);
  static const goldLight  = Color(0xFFE8C060);

  /// ألوان الوضع الليلي (الداكن) للخلفية والأسطح والنصوص.
  // ─── Dark Mode ─────────────────────────────────
  static const darkBg        = Color(0xFF060D1A);
  static const darkSurface   = Color(0xFF0D1E2C);
  static const darkSurface2  = Color(0xFF152839);
  static const darkBorder    = Color(0x1FFFFFFF);
  static const darkBorderAlt = Color(0x33FFFFFF);
  static const darkTextPrimary   = Color(0xFFEAF2F8);
  static const darkTextSecondary = Color(0xFF7B9BB5);
  static const darkTextMuted     = Color(0xFF5A8DB0);

  /// ألوان الوضع النهاري (الفاتح) للخلفية والأسطح والنصوص.
  // ─── Light Mode ────────────────────────────────
  static const lightBg        = Color(0xFFDDEAF6);
  static const lightSurface   = Color(0x8CFFFFFF);
  static const lightBorder    = Color(0xCCFFFFFF);
  static const lightBorderSub = Color(0x40FFFFFF);
  static const lightTextPrimary   = Color(0xFF1B3A52);
  static const lightTextSecondary = Color(0xFF3A6B85);
  static const lightTextMuted     = Color(0xFF7B9BB5);

  /// ألوان تأثيرات التوهج للعناصر الكروية في الوضعين النهاري والليلي.
  // ─── Orb Glows ─────────────────────────────────
  static const orbBlueDark  = Color(0x332D5F8A);
  static const orbGoldDark  = Color(0x26C49A2B);
  static const orbBlueLight = Color(0x2E4A8DBF);
  static const orbGoldLight = Color(0x23C49A2B);

  /// ألوان حالة النجاح والتحذير والخطأ والمعلومات.
  // ─── Status ────────────────────────────────────
  static const success = Color(0xFF2ECC8A);
  static const warning = Color(0xFFF0A500);
  static const danger  = Color(0xFFE24B4A);
  static const info    = Color(0xFF4A8DBF);
}
