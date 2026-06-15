import 'package:flutter/material.dart';
import '../../config/colors.dart';

/// زر إجراء رئيسي منمّق مع حالة تحميل وأيقونة اختيارية وألوان وأبعاد قابلة للتخصيص
class PrimaryButton extends StatelessWidget {
  /// The button label text.
  final String label;

  /// Called when the button is pressed (disabled during loading).
  final VoidCallback? onPressed;

  /// If true, shows a circular progress indicator instead of the label.
  final bool isLoading;

  /// Background color (defaults to [MaskanColors.kBlue]).
  final Color? backgroundColor;

  /// Foreground (text/icon) color.
  final Color? foregroundColor;

  /// Button height (default 52).
  final double height;

  /// Button width (defaults to full width).
  final double? width;

  /// Optional icon shown alongside the label.
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
