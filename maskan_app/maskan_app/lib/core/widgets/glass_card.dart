import 'dart:ui';
import 'package:flutter/material.dart';
import '../design/design_tokens.dart';
import '../../config/colors.dart';

/// الأنواع المختلفة لبطاقة [GlassCard]، لكل منها ألوان وظلال مميزة
///
/// * [normal] — مظهر زجاجي شفاف عادي
/// * [blue] — صبغة زرقاء للحالات المحددة
/// * [gold] — صبغة ذهبية للتمييز
/// * [strong] — نسخة أكثر عتامة للتأكيد
enum GlassVariant { normal, blue, gold, strong }

/// بطاقة بتأثير الزجاج (Glassmorphism) مع خلفية ضبابية وألوان حسب النوع
class GlassCard extends StatelessWidget {
  /// محتوى البطاقة
  final Widget child;
  /// زوايا البطاقة المدورة (افتراضي 16)
  final double borderRadius;
  /// الحشوة الداخلية
  final EdgeInsetsGeometry? padding;
  /// الهامش الخارجي
  final EdgeInsetsGeometry? margin;
  /// قوة الضبابية (افتراضي 14)
  final double blurStrength;
  /// النوع البصري الذي يحدد الألوان والظلال
  final GlassVariant variant;
  /// عرض ثابت اختياري
  final double? width;
  /// ارتفاع ثابت اختياري
  final double? height;
  /// إذا كان true يستخدم حاوية بسيطة بدون ضبابية للأداء
  final bool softMode;

  const GlassCard({
    super.key,
    required this.child,
    this.borderRadius = 16,
    this.padding,
    this.margin,
    this.blurStrength = 14,
    this.variant = GlassVariant.normal,
    this.width,
    this.height,
    this.softMode = false,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (softMode) {
      Color bgColor;
      switch (variant) {
        case GlassVariant.blue:
          bgColor = isDark ? const Color(0x1A4A8DBF) : const Color(0x1A4A8DBF);
          break;
        case GlassVariant.gold:
          bgColor = isDark ? const Color(0x1AC49A2B) : const Color(0x1FC49A2B);
          break;
        case GlassVariant.strong:
          bgColor = isDark ? MaskanColors.kBgCard2 : const Color(0xF5FFFFFF);
          break;
        default:
          bgColor = isDark ? MaskanColors.kBgCard2 : const Color(0xF0FFFFFF);
      }
      return Container(
        width: width,
        height: height,
        margin: margin,
        padding: padding ?? const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(borderRadius),
          boxShadow: isDark
              ? DesignTokens.softShadowDark()
              : DesignTokens.softShadowLight(),
        ),
        child: child,
      );
    }

    Color bgColor;
    Color borderColor;
    List<BoxShadow> shadows;

    switch (variant) {
      case GlassVariant.blue:
        bgColor = isDark ? const Color(0x2A4A8DBF) : const Color(0x1A4A8DBF);
        borderColor = isDark ? const Color(0x404A8DBF) : const Color(0x404A8DBF);
        shadows = isDark
            ? [BoxShadow(color: MaskanColors.kBlue.withValues(alpha: 0.12), blurRadius: 20, offset: const Offset(0, 4))]
            : [BoxShadow(color: MaskanColors.kBlue.withValues(alpha: 0.08), blurRadius: 20, offset: const Offset(0, 6))];
        break;
      case GlassVariant.gold:
        bgColor = isDark ? const Color(0x24C49A2B) : const Color(0x1FC49A2B);
        borderColor = isDark ? const Color(0x40C49A2B) : const Color(0x4CC49A2B);
        shadows = isDark
            ? [BoxShadow(color: MaskanColors.kGold.withValues(alpha: 0.12), blurRadius: 20, offset: const Offset(0, 4))]
            : [BoxShadow(color: MaskanColors.kGold.withValues(alpha: 0.08), blurRadius: 20, offset: const Offset(0, 6))];
        break;
      case GlassVariant.strong:
        bgColor = isDark ? const Color(0x38FFFFFF) : const Color(0xE0FFFFFF);
        borderColor = isDark ? const Color(0x4DFFFFFF) : const Color(0xCCFFFFFF);
        shadows = isDark
            ? DesignTokens.softShadowDark()
            : DesignTokens.softShadowLight();
        break;
      default:
        bgColor = isDark ? const Color(0x24FFFFFF) : const Color(0xE8FFFFFF);
        borderColor = isDark ? const Color(0x18FFFFFF) : const Color(0xCCFFFFFF);
        shadows = isDark
            ? [BoxShadow(color: const Color(0xFF000000).withValues(alpha: 0.15), blurRadius: 20, offset: const Offset(0, 4))]
            : [BoxShadow(color: const Color(0xFF8BAAC4).withValues(alpha: 0.10), blurRadius: 24, offset: const Offset(0, 6))];
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(borderRadius),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: blurStrength, sigmaY: blurStrength),
        child: Container(
          width: width,
          height: height,
          margin: margin,
          padding: padding ?? const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(borderRadius),
            border: Border.all(color: borderColor, width: 0.6),
            boxShadow: [
              ...shadows,
              BoxShadow(
                color: isDark ? const Color(0xFFFFFFFF).withValues(alpha: 0.02) : const Color(0xFFFFFFFF).withValues(alpha: 0.25),
                blurRadius: 0,
                offset: const Offset(0, 1),
              ),
            ],
          ),
          child: child,
        ),
      ),
    );
  }
}
