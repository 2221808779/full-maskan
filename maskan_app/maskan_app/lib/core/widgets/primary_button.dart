import 'package:flutter/material.dart';
import '../../config/colors.dart';

/// زر إجراء رئيسي منمّق مع حالة تحميل وأيقونة اختيارية وألوان وأبعاد قابلة للتخصيص
class PrimaryButton extends StatelessWidget {
  /// نص تسمية الزر.
  final String label;

  /// يُستدعى عند ضغط الزر (معطل أثناء التحميل).
  final VoidCallback? onPressed;

  /// إذا كان true، يعرض مؤشر تقدم بدلاً من النص.
  final bool isLoading;

  /// لون الخلفية (الافتراضي [MaskanColors.kBlue]).
  final Color? backgroundColor;

  /// لون المقدمة (النص/الأيقونة).
  final Color? foregroundColor;

  /// ارتفاع الزر (الافتراضي 52).
  final double height;

  /// عرض الزر (الافتراضي العرض الكامل).
  final double? width;

  /// أيقونة اختيارية تظهر بجانب النص.
  final IconData? icon;

  const PrimaryButton({
    super.key,
    required this.label,
    this.onPressed,
    this.isLoading = false,
    this.backgroundColor,
    this.foregroundColor,
    this.height = 52,
    this.width,
    this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width ?? double.infinity,
      height: height,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: backgroundColor ?? MaskanColors.kBlue,
          foregroundColor: foregroundColor ?? Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          elevation: 0,
          textStyle: const TextStyle(
            fontFamily: 'Cairo',
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        child: isLoading
            ? const SizedBox(
                height: 20, width: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              )
            : icon != null
                ? Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(icon, size: 18),
                      const SizedBox(width: 8),
                      Text(label),
                    ],
                  )
                : Text(label),
      ),
    );
  }
}
