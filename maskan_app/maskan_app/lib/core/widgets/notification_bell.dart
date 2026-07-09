import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../config/routes.dart';
import '../../providers/notification_provider.dart';

/// زر أيقونة يعرض عدد الإشعارات غير المقروءة كشارة
/// عند الضغط ينتقل إلى شاشة الإشعارات
class NotificationBell extends StatelessWidget {
  /// لون أيقونة الجرس.
  final Color iconColor;

  /// حجم أيقونة الجرس.
  final double iconSize;

  const NotificationBell({
    super.key,
    this.iconColor = MaskanColors.kBlue,
    this.iconSize = 24,
  });

  @override
  Widget build(BuildContext context) {
    final unreadCount = context.watch<NotificationProvider>().unreadCount;
    final showBadge = unreadCount > 0;

    return Stack(
      clipBehavior: Clip.none,
      children: [
        IconButton(
          icon: Icon(Icons.notifications_outlined, color: iconColor, size: iconSize),
          onPressed: () => context.push(AppRoutes.notifications),
        ),
        if (showBadge)
          Positioned(
            top: 6,
            right: 6,
            child: Container(
              constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
              decoration: BoxDecoration(
                color: MaskanColors.danger,
                borderRadius: BorderRadius.circular(9),
                border: Border.all(color: Colors.white, width: 1.5),
              ),
              child: Text(
                unreadCount > 99 ? '99+' : '$unreadCount',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Cairo',
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }
}
