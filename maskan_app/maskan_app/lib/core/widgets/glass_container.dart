import 'dart:ui';
import 'package:flutter/material.dart';
import '../../config/colors.dart';

/// حاوية زجاجية (Frosted Glass) مع تأثير ضبابي للخلفية قابلة للتخصيص — الحدود، الحواف، الهوامش، واللون
class GlassContainer extends StatelessWidget {
  /// المحتوى المعروض داخل الحاوية الزجاجية.
  final Widget child;

  /// نصف قطر زوايا الحاوية (الافتراضي 16).
  final double borderRadius;

  /// الحشوة الداخلية.
  final EdgeInsetsGeometry? padding;

  /// الهامش الخارجي.
  final EdgeInsetsGeometry? margin;

  /// قيمة سيجما لفلتر التمويه الخلفي (الافتراضي 12).
  final double blur;

  /// لون الصبغة الخلفية المطبق على الطبقة الزجاجية.
  final Color tint;

  /// لون حدود مخصص اختياري (الافتراضي [MaskanColors.kGlassBorder]).
  final Color? borderColor;

  /// عرض الحدود (الافتراضي 0.5).
  final double borderWidth;

  /// ارتفاع ثابت اختياري.
  final double? height;

  /// عرض ثابت اختياري.
  final double? width;

  const GlassContainer({
    super.key,
    required this.child,
    this.borderRadius = 16,
    this.padding,
    this.margin,
    this.blur = 12,
    this.tint = MaskanColors.kGlassLight,
    this.borderColor,
    this.borderWidth = 0.5,
    this.height,
    this.width,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveBorder = borderColor ?? MaskanColors.kGlassBorder;
    return Container(
      margin: margin,
      height: height,
      width: width,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(borderRadius),
        border: Border.all(color: effectiveBorder, width: borderWidth),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(borderRadius),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: blur, sigmaY: blur),
          child: Container(
            padding: padding ?? const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: tint,
              borderRadius: BorderRadius.circular(borderRadius),
            ),
            child: child,
          ),
        ),
      ),
    );
  }
}

/// شارة ذهبية تعرض تصنيفاً (مثل نوع المستخدم)
class GoldBadge extends StatelessWidget {
  /// النص المعروض داخل الشارة.
  final String label;

  /// حجم خط النص (الافتراضي 12).
  final double fontSize;

  const GoldBadge({super.key, required this.label, this.fontSize = 12});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: MaskanColors.kGold.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: MaskanColors.kGold.withValues(alpha: 0.5)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: MaskanColors.kGold,
          fontSize: fontSize,
          fontWeight: FontWeight.w600,
          fontFamily: 'Cairo',
        ),
      ),
    );
  }
}

/// شريحة صغيرة تعرض أيقونة ونصاً لمواصفات العقار
class SpecChip extends StatelessWidget {
  /// الأيقونة التي تمثل المواصفة.
  final IconData icon;

  /// النص الذي يصف المواصفة.
  final String label;

  const SpecChip({super.key, required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: MaskanColors.kBgInput,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: MaskanColors.kTextSecondary),
          const SizedBox(width: 4),
          Text(label, style: const TextStyle(
            fontSize: 13, color: MaskanColors.kTextSecondary, fontFamily: 'Cairo',
          )),
        ],
      ),
    );
  }
}

/// شارة ملونة لعرض تسميات الحالة (مثل حالة الحجز)
class StatusBadge extends StatelessWidget {
  /// نص الحالة المعروض.
  final String label;

  /// لون صبغة خلفية الشارة ونصها.
  final Color color;

  const StatusBadge({super.key, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.bold,
          fontFamily: 'Cairo',
        ),
      ),
    );
  }
}
