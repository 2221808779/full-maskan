import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/colors.dart';
import '../../providers/notification_provider.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/utils/helpers.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض قائمة الإشعارات مع أيقونات وألوان حسب النوع
/// تدعم تعيين إشعار فردي أو الكل كمقروء وإزالة الإشعارات بالسحب
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

/// منطق حالة [NotificationsScreen] — تحميل الإشعارات وإدارة حالة القراءة والتنسيق حسب النوع
class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().loadNotifications();
    });
  }

  /// يُرجع أيقونة مناسبة لنوع الإشعار.
  IconData _iconForType(String type) {
    switch (type) {
      case 'booking': return Icons.calendar_today;
      case 'maintenance': return Icons.build_outlined;
      case 'message': return Icons.message_outlined;
      case 'review': return Icons.star_outline;
      default: return Icons.notifications_outlined;
    }
  }

  /// يُرجع لوناً مرتبطاً بنوع الإشعار.
  Color _colorForType(String type) {
    switch (type) {
      case 'booking': return MaskanColors.kBlue;
      case 'maintenance': return MaskanColors.kWarning;
      case 'message': return MaskanColors.kSuccess;
      case 'review': return MaskanColors.kGold;
      default: return context.textMuted;
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<NotificationProvider>();
    return MaskanScaffold(
      appBar: AppBar(
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Text(AppLocalizations.of(context)!.notificationsTitle, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        centerTitle: true,
        actions: [
          if (provider.unreadCount > 0)
            TextButton(
              onPressed: () => provider.markAllAsRead(),
              child: Text(AppLocalizations.of(context)!.markAllRead, style: TextStyle(
                color: MaskanColors.kBlue, fontFamily: 'Cairo', fontSize: 13,
              )),
            ),
        ],
      ),
      body: provider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : provider.notifications.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.notifications_off_outlined,
                        color: context.textMuted, size: 64),
                      const SizedBox(height: 16),
                      Text(AppLocalizations.of(context)!.noNotifications, style: TextStyle(
                        color: context.textSecondary, fontFamily: 'Cairo',
                      )),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  itemCount: provider.notifications.length,
                  separatorBuilder: (_, _) => const Divider(
                    height: 1, color: MaskanColors.kGlassBorder,
                  ),
                  itemBuilder: (_, i) {
                    final n = provider.notifications[i];
                    final type = n.type ?? '';
                    final color = _colorForType(type);
                    return Dismissible(
                      key: Key('${n.id}'),
                      direction: DismissDirection.endToStart,
                      background: Container(
                        alignment: Alignment.centerRight,
                        padding: const EdgeInsets.only(right: 20),
                        color: MaskanColors.kDanger,
                        child: const Icon(Icons.delete_outline, color: Colors.white),
                      ),
                      onDismissed: (_) {
                        provider.removeNotification(n.id);
                        provider.markAsRead(n.id);
                      },
                      child: Container(
                        height: 72,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: Row(
                          children: [
                            Container(
                              width: 40, height: 40,
                              decoration: BoxDecoration(
                                color: color.withValues(alpha: 0.15),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(_iconForType(type), color: color, size: 20),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(n.title, style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: n.isRead ? FontWeight.normal : FontWeight.w600,
                                          color: context.textPrimary, fontFamily: 'Cairo',
                                        )),
                                      ),
                                      if (!n.isRead)
                                        Container(
                                          width: 8, height: 8,
                                          decoration: const BoxDecoration(
                                            color: MaskanColors.kBlue,
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 2),
                                  Text(n.content, style: TextStyle(
                                    fontSize: 13, color: context.textSecondary,
                                    fontFamily: 'Cairo',
                                  ), maxLines: 1, overflow: TextOverflow.ellipsis),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(Helpers.timeAgo(n.createdAt), style: TextStyle(
                              fontSize: 12, color: context.textMuted,
                              fontFamily: 'Cairo',
                            )),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
