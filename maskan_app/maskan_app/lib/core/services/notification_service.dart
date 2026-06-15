import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import '../api/api_client.dart';
import '../../config/constants.dart';
import '../../models/notification.dart';

/// خدمة الإشعارات — تدير الإشعارات المحلية والفورية
///
/// - [init] تهيئة الإشعارات المحلية والاتصال بـ Pusher
/// - [subscribeToUserChannel] الاشتراك في قناة مستخدم لاستقبال إشعاراته
/// - [onNotificationReceived] callback يتم استدعاؤه عند وصول إشعار جديد
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  /// مكون الإشعارات المحلية لنظامي Android و iOS
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
  /// عميل Pusher للإشعارات الفورية
  PusherChannelsFlutter? _pusher;
  /// callback يتم استدعاؤه عند استقبال إشعار جديد من Pusher
  Function(AppNotification)? onNotificationReceived;

  /// تهيئة خدمة الإشعارات — تفعيل الإشعارات المحلية والاتصال بـ Pusher
  Future<void> init() async {
    if (!kIsWeb) {
      const androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
      const iosSettings = DarwinInitializationSettings();
      await _localNotifications.initialize(
        const InitializationSettings(android: androidSettings, iOS: iosSettings),
        onDidReceiveNotificationResponse: _onNotificationTap,
      );
      _initPusher();
    }
  }

  /// معالجة النقر على الإشعار المحلي — تحليل الـ payload إن وُجد
  void _onNotificationTap(NotificationResponse response) {
    final payload = response.payload;
    if (payload != null) {
      try {
        jsonDecode(payload);
      } catch (_) {}
    }
  }

  /// تهيئة الاتصال بـ Pusher/Reverb والاستماع للأحداث
  void _initPusher() {
    try {
      _pusher = PusherChannelsFlutter.getInstance();
      _pusher!.init(
        apiKey: AppConstants.reverbAppKey,
        cluster: 'mt1',
        host: AppConstants.reverbHost,
        wsPort: AppConstants.reverbPort,
        wssPort: AppConstants.reverbPort,
        useTLS: AppConstants.reverbUseTLS,
        authEndpoint: kIsWeb ? AppConstants.authEndpoint : null,
        onAuthorizer: kIsWeb ? null : _onAuthorizer,
        onConnectionStateChange: (current, previous) {
          debugPrint('Pusher connection: $previous -> $current');
        },
        onError: (message, code, error) {
          debugPrint('Pusher error: $message (code: $code, error: $error)');
        },
        onSubscriptionError: (message, error) {
          debugPrint('Pusher subscription error: $message (error: $error)');
        },
        onEvent: (event) {
          try {
            debugPrint('Pusher event received: ${event.eventName} on ${event.channelName}');
            final data = event.data is Map
                ? Map<String, dynamic>.from(event.data as Map)
                : jsonDecode(event.data.toString()) as Map<String, dynamic>;
            debugPrint('Pusher event data: $data');
            final notification = AppNotification.fromJson(data);
            if (onNotificationReceived != null) {
              onNotificationReceived!(notification);
            }
            _showLocalNotification(notification.title, notification.content);
          } catch (e) {
            debugPrint('Pusher event parse error: $e');
          }
        },
      );
      _pusher!.connect();
    } catch (e) {
      debugPrint('Pusher init error: $e');
    }
  }

  /// مصادقة القنوات الخاصة عبر API (لإرسال الكوكيز مع الطلب)
  /// يستخدم فقط على الأجهزة (mobile)، أما الـ web فالمتصفح يرسل الكوكيز تلقائياً
  Future<Map<String, String>?> _onAuthorizer(
      String channelName, String socketId, dynamic options) async {
    try {
      final authUrl = AppConstants.baseUrl.replaceAll('/api', '/broadcasting/auth');
      final response = await ApiClient().post(authUrl, data: {
        'channel_name': channelName,
        'socket_id': socketId,
      });
      final data = response.data as Map<String, dynamic>;
      return {
        'auth': data['auth']?.toString() ?? '',
      };
    } catch (e) {
      debugPrint('Pusher auth error: $e');
      return null;
    }
  }

  /// الاشتراك في قناة مستخدم معين لاستقبال إشعاراته الفورية
  Future<void> subscribeToUserChannel(int userId) async {
    try {
      await _pusher?.subscribe(channelName: 'private-user.$userId');
    } catch (_) {}
  }

  /// إلغاء الاشتراك من قناة مستخدم معين
  Future<void> unsubscribeUserChannel(int userId) async {
    try {
      await _pusher?.unsubscribe(channelName: 'private-user.$userId');
    } catch (_) {}
  }

  /// الاشتراك في قناة محادثة معينة لاستقبال إشعارات الرسائل
  Future<void> subscribeToConversation(int conversationId) async {
    try {
      await _pusher?.subscribe(channelName: 'conversation.$conversationId');
    } catch (_) {}
  }

  /// إلغاء الاشتراك من قناة Pusher محددة
  Future<void> unsubscribeFromChannel(String channel) async {
    try {
      await _pusher?.unsubscribe(channelName: channel);
    } catch (_) {}
  }

  /// عرض إشعار محلي على الجهاز بنظامي Android و iOS
  Future<void> _showLocalNotification(String title, String body, {String? payload}) async {
    const androidDetails = AndroidNotificationDetails(
      'maskan_channel',
      'إشعارات مسكن',
      channelDescription: 'إشعارات تطبيق مسكن',
      importance: Importance.high,
      priority: Priority.high,
    );
    const details = NotificationDetails(android: androidDetails);
    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title,
      body,
      details,
      payload: payload,
    );
  }

  /// تعيين إشعار معين كمقروء عبر API
  Future<void> markAsRead(int notificationId) async {
    try {
      await ApiClient().post('/notifications/$notificationId/read');
    } catch (_) {}
  }

  /// تعيين جميع الإشعارات كمقروءة عبر API
  Future<void> markAllAsRead() async {
    try {
      await ApiClient().post('/notifications/read-all');
    } catch (_) {}
  }

  /// التخلص من موارد الخدمة وفصل الاتصال بـ Pusher
  void dispose() {
    _pusher?.disconnect();
  }
}
