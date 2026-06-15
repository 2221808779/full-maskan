import 'package:flutter/material.dart';
import '../../config/colors.dart';

/// عرض حالة فارغة في وسط الشاشة مع أيقونة وعنوان ونص فرعي وزر اختياري
class EmptyState extends StatelessWidget {
  /// الأيقونة المعروضة في الأعلى
  final IconData icon;
  /// النص الرئيسي
  final String title;
  /// نص فرعي اختياري
  final String? subtitle;
  /// نص الزر الاختياري
  final String? actionLabel;
  /// دالة الضغط على الزر
  final VoidCallback? onAction;

  const EmptyState({
    super.key,
    this.icon = Icons.inbox_outlined,
    required this.title,
    this.subtitle,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 80, color: MaskanColors.kBlue),
            const SizedBox(height: 16),
            Text(
              title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: MaskanColors.kBlue,
              ),
              textAlign: TextAlign.center,
            ),
            if (subtitle != null) ...[
              const SizedBox(height: 8),
              Text(
                subtitle!,
                style: TextStyle(fontSize: 14, color: MaskanColors.kBlue),
                textAlign: TextAlign.center,
              ),
            ],
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: onAction,
                child: Text(actionLabel!),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
