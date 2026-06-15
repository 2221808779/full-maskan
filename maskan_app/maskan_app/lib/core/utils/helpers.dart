import 'package:intl/intl.dart';
import 'package:flutter/material.dart';

/// دوال مساعدة — دوال ثابتة لتنسيق التواريخ والأسعار وعرض التنبيهات
class Helpers {
  /// تنسيق تاريخ (yyyy/MM/dd) باللغة العربية
  static String formatDate(DateTime date) {
    return DateFormat('yyyy/MM/dd', 'ar').format(date);
  }

  /// تنسيق تاريخ ووقت (yyyy/MM/dd HH:mm) من نص — تعيد فارغاً إن كان null
  static String formatDateTime(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('yyyy/MM/dd HH:mm', 'ar').format(date);
    } catch (_) {
      return dateStr;
    }
  }

  /// تنسيق سعر بالعملة المحلية (د.ل) مع فواصل الآلاف
  static String formatPrice(double price) {
    return '${NumberFormat('#,##0', 'ar').format(price)} د.ل';
  }

  /// تحويل تاريخ نصي إلى عبارة زمنية نسبية (منذ دقيقة، منذ ساعة، إلخ)
  static String timeAgo(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final date = DateTime.parse(dateStr);
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inMinutes < 1) return 'الآن';
      if (diff.inMinutes == 1) return 'منذ دقيقة';
      if (diff.inMinutes < 60) return 'منذ ${diff.inMinutes} دقائق';
      if (diff.inHours == 1) return 'منذ ساعة';
      if (diff.inHours < 24) return 'منذ ${diff.inHours} ساعات';
      if (diff.inDays == 1) return 'منذ يوم';
      if (diff.inDays < 7) return 'منذ ${diff.inDays} أيام';
      return formatDate(date);
    } catch (_) {
      return dateStr;
    }
  }

  /// عرض SnackBar عائم مع رسالة ولون حسب نوعها (خطأ/نجاح)
  static void showSnackBar(BuildContext context, String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : Colors.green,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  /// عرض حوار تحميل (منع تفاعل المستخدم) إلى أن يُغلق
  static void showLoadingDialog(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );
  }
}
