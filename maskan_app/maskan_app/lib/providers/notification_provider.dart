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

  /// The list of all notifications.
  List<AppNotification> get notifications => _notifications;
  /// The count of unread notifications.
  int get unreadCount => _unreadCount;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;

  /// Sets up the real-time notification listener via [NotificationService].
  NotificationProvider() {
    NotificationService().onNotificationReceived = (notification) {
      _notifications.insert(0, notification);
      _unreadCount++;
      notifyListeners();
    };
  }

  /// Loads all notifications from the API and updates the unread count.
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

  /// Loads only the unread notification count from the API.
  Future<void> loadUnreadCount() async {
    try {
      final response = await _api.get(ApiEndpoints.unreadCount);
      _unreadCount = response.data['count'] ?? response.data['data'] ?? 0;
      notifyListeners();
    } catch (_) {}
  }

  /// Marks a single notification as read by its [id].
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

  /// Marks all notifications as read.
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

  /// Adds a new notification to the top of the list (e.g., from a push notification).
  Future<void> addNotification(AppNotification n) async {
    _notifications.insert(0, n);
    _unreadCount++;
    notifyListeners();
  }

  /// Removes a notification by its [id] from the local list.
  void removeNotification(int id) {
    _notifications.removeWhere((n) => n.id == id);
    _unreadCount = _notifications.where((n) => !n.isRead).length;
    notifyListeners();
  }
}
