import 'package:flutter/material.dart';
import '../../config/colors.dart';

/// رموز التصميم الموحدة — تحتوي على قيم ثابتة للتباعد، الأحجام، الظلال، والرسوم المتحركة
class DesignTokens {
  DesignTokens._();

  static const double radiusXS  =  8.0;
  static const double radiusSM  = 12.0;
  static const double radiusMD  = 16.0;
  static const double radiusLG  = 20.0;
  static const double radiusXL  = 24.0;
  static const double radius2XL = 32.0;

  static const double space4  =  4.0;
  static const double space8  =  8.0;
  static const double space12 = 12.0;
  static const double space16 = 16.0;
  static const double space20 = 20.0;
  static const double space24 = 24.0;
  static const double space32 = 32.0;
  static const double space48 = 48.0;

  static const EdgeInsets pagePadding    = EdgeInsets.symmetric(horizontal: 20);
  static const EdgeInsets cardPadding    = EdgeInsets.all(16);
  static const EdgeInsets sectionSpacing = EdgeInsets.only(top: 24, bottom: 8);

  static const double fontH1     = 26.0;
  static const double fontH2     = 22.0;
  static const double fontH3     = 18.0;
  static const double fontTitle  = 15.0;
  static const double fontBody   = 14.0;
  static const double fontSmall  = 12.0;
  static const double fontXS     = 10.0;

  static const Duration fast    = Duration(milliseconds: 180);
  static const Duration normal  = Duration(milliseconds: 280);
  static const Duration slow    = Duration(milliseconds: 400);
  static const Duration navAnim = Duration(milliseconds: 320);

  static const double blurLight  =  8.0;
  static const double blurMedium = 14.0;
  static const double blurHeavy  = 20.0;

  /// ظلال ناعمة للوضع الفاتح — مع معامل شدة قابل للتعديل
  static List<BoxShadow> softShadowLight({double intensity = 1.0}) => [
    BoxShadow(
      color: const Color(0xFFFFFFFF).withValues(alpha: 0.8 * intensity),
      offset: Offset(-4 * intensity, -4 * intensity),
      blurRadius: 12 * intensity,
      spreadRadius: 0,
    ),
    BoxShadow(
      color: const Color(0xFF8BAAC4).withValues(alpha: 0.25 * intensity),
      offset: Offset(4 * intensity, 4 * intensity),
      blurRadius: 12 * intensity,
      spreadRadius: 0,
    ),
  ];

  /// ظلال ناعمة للوضع الداكن — مع معامل شدة قابل للتعديل
  static List<BoxShadow> softShadowDark({double intensity = 1.0}) => [
    BoxShadow(
      color: MaskanColors.kBlue.withValues(alpha: 0.12 * intensity),
      offset: Offset(-2 * intensity, -2 * intensity),
      blurRadius: 10 * intensity,
      spreadRadius: 0,
    ),
    BoxShadow(
      color: const Color(0xFF000000).withValues(alpha: 0.35 * intensity),
      offset: Offset(3 * intensity, 3 * intensity),
      blurRadius: 10 * intensity,
      spreadRadius: 0,
    ),
  ];
}
