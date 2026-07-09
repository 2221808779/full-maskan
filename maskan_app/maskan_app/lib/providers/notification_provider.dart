import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../core/services/notification_service.dart';
import '../models/notification.dart';

/// يدير الإشعارات — تحميل الإشعارات، تعيينها كمقروءة، وتتبع العدد غير المقروء
/// يتواصل مع API الإشعارات لجلب القائمة
/// يشترك في قنوات Pusher للإشعارات الفورية عبر المنشئ
/// يوفر [unreadCount] لعرض الشارة
class NotificationProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<AppNotification> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;

  /// قائمة جميع الإشعارات.
  List<AppNotification> get notifications => _notifications;
  /// عدد الإشعارات غير المقروءة.
  int get unreadCount => _unreadCount;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;

  /// يُعدّد مستمع الإشعارات الفورية عبر [NotificationService].
  NotificationProvider() {
    NotificationService().onNotificationReceived = (notification) {
      _notifications.insert(0, notification);
      _unreadCount++;
      notifyListeners();
    };
  }

  /// يحمّل جميع الإشعارات من API ويحدّث العدد غير المقروء.
  Future<void> loadNotifications() async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.notifications);
      final List<dynamic> list = response.data['data'] ?? [];
      _notifications = list.map((j) => AppNotification.fromJson(j)).toList();
      _unreadCount = _notifications.where((n) => !n.isRead).length;
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// يحمّل عدد الإشعارات غير المقروءة فقط من API.
  Future<void> loadUnreadCount() async {
    try {
      final response = await _api.get(ApiEndpoints.unreadCount);
      _unreadCount = response.data['count'] ?? response.data['data'] ?? 0;
      notifyListeners();
    } catch (_) {}
  }

  /// يعلّم إشعاراً واحداً كمقروء بواسطة [id].
  Future<void> markAsRead(int id) async {
    try {
      await _api.put(ApiEndpoints.markNotificationRead(id));
      final index = _notifications.indexWhere((n) => n.id == id);
      if (index != -1) {
        _notifications[index] = AppNotification(
          id: _notifications[index].id,
          userId: _notifications[index].userId,
          type: _notifications[index].type,
          title: _notifications[index].title,
          content: _notifications[index].content,
          isRead: true,
          createdAt: _notifications[index].createdAt,
          updatedAt: _notifications[index].updatedAt,
        );
        _unreadCount = _notifications.where((n) => !n.isRead).length;
        notifyListeners();
      }
    } catch (_) {}
  }

  /// يعلّم جميع الإشعارات كمقروءة.
  Future<void> markAllAsRead() async {
    try {
      await _api.put(ApiEndpoints.markAllRead);
      _notifications = _notifications.map((n) => AppNotification(
        id: n.id,
        userId: n.userId,
        type: n.type,
        title: n.title,
        content: n.content,
        isRead: true,
        createdAt: n.createdAt,
        updatedAt: n.updatedAt,
      )).toList();
      _unreadCount = 0;
      notifyListeners();
    } catch (_) {}
  }

  /// يضيف إشعاراً جديداً إلى أعلى القائمة (مثلاً من إشعار فوري).
  Future<void> addNotification(AppNotification n) async {
    _notifications.insert(0, n);
    _unreadCount++;
    notifyListeners();
  }

  /// يزيل إشعاراً بواسطة [id] من القائمة المحلية.
  void removeNotification(int id) {
    _notifications.removeWhere((n) => n.id == id);
    _unreadCount = _notifications.where((n) => !n.isRead).length;
    notifyListeners();
  }
}
