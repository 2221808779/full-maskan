import 'package:flutter/material.dart';

/// ألوان العلامة التجارية وخلفيات/نصوص الوضعين الفاتح والداكن
class MaskanColors {
  MaskanColors._();

  // ── Brand ──
  static const kBlueDark   = Color(0xFF1B3A52);
  static const kBlue       = Color(0xFF1A3A5C);
  static const kBlueLight  = Color(0xFF4A8DBF);
  static const kBlueSky    = Color(0xFF6BB8E8);
  static const kGoldDark   = Color(0xFFA3700E);
  static const kGold       = Color(0xFFC49A2B);
  static const kGoldLight  = Color(0xFFE8C060);

  // ── Gray scale ──
  static const gray50  = Color(0xFFF7F8FA);
  static const gray100 = Color(0xFFEDEEF0);
  static const gray200 = Color(0xFFDDE0E4);
  static const gray400 = Color(0xFF989EA7);
  static const gray600 = Color(0xFF6A717B);
  static const gray800 = Color(0xFF2C3138);
  static const gray900 = Color(0xFF1A1D22);

  // ── Dark surfaces ──
  static const kBgDark     = Color(0xFF060D1A);
  static const kBgCard     = Color(0xFF0D1E2C);
  static const kBgCard2    = Color(0xFF152839);
  static const kBgInput    = Color(0x26FFFFFF);
  static const kGlassLight = Color(0x12FFFFFF);
  static const kGlassBorder = Color(0x1FFFFFFF);

  // ── Light surfaces ──
  static const lBg        = Color(0xFFF7F8FA);
  static const lBgCard    = Color(0xFFFFFFFF);
  static const lBgInput   = Color(0xFFF7F8FA);
  static const lBorder    = Color(0xFFEDEEF0);
  static const lBorderSub = Color(0xFFDDE0E4);

  // ── Dark text ──
  static const kTextPrimary   = Color(0xFFEAF2F8);
  static const kTextSecondary = Color(0xFF7B9BB5);
  static const kTextMuted     = Color(0xFF5A8DB0);

  // ── Light text ──
  static const lTextPrimary   = Color(0xFF0F2A42); // أزرق غامق جداً
  static const lTextSecondary = Color(0xFF2A6A96); // أزرق متوسط
  static const lTextMuted     = Color(0xFF4A6F89); // رمادي-أزرق داكن

  // ── Status ──
  static const success   = Color(0xFF1A8F4C);
  static const successBg = Color(0xFFE3F5EB);
  static const warning   = Color(0xFFF0A500);
  static const danger    = Color(0xFFC0392B);
  static const dangerBg  = Color(0xFFF9E4E2);
  static const kDanger   = danger;
  static const kSuccess  = success;
  static const kWarning  = warning;
}

/// إضافات للحصول على ألوان النصوص بناءً على وضع الثيم (فاتح/داكن)
extension MaskanTextColors on BuildContext {
  /// لون النص الأساسي حسب وضع الثيم
  Color get textPrimary => isDarkMode ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary;
  /// لون النص الثانوي حسب وضع الثيم
  Color get textSecondary => isDarkMode ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary;
  /// لون النص الخافت حسب وضع الثيم
  Color get textMuted => isDarkMode ? MaskanColors.kTextMuted : MaskanColors.lTextMuted;
  /// هل الثيم في الوضع الداكن؟
  bool get isDarkMode => Theme.of(this).brightness == Brightness.dark;
}
