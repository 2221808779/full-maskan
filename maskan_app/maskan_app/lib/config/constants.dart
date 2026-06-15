import 'package:flutter/foundation.dart';

/// الثوابت العامة لتطبيق مسكن
///
/// تحتوي على جميع الإعدادات الثابتة التي يحتاجها التطبيق:
/// - روابط API الخلفي (Laravel)
/// - إعدادات Pusher للإشعارات الفورية
/// - إعدادات بوابة الدفع Plutu
/// - إعدادات عامة (حجم الصفحة، طول رمز التحقق، إلخ)
class AppConstants {
  /// اسم التطبيق المعروض للمستخدم
  static const String appName = 'مسكن';

  /// رابط API الخلفي (Laravel) — يستخدمه Dio لجميع الطلبات
  static const String baseUrl = 'http://192.168.1.111:8000/api/';

  /// رابط تخزين الصور (Storage) — لتحميل صور العقارات والملفات الشخصية
  static String get imageBaseUrl =>
      kIsWeb ? 'http://127.0.0.1:8000/storage' : 'http://192.168.1.111:8000/storage';

  /// مهلة الاتصال بالخادم (بالثواني)
  static const Duration timeout = Duration(seconds: 30);

  /// بناء رابط صورة كامل — يتعامل مع وجود/عدم وجود بادئة storage/
  static String resolveImageUrl(String path) {
    if (path.startsWith('http')) return path;
    final clean = path.startsWith('storage/') ? path.substring(8) : path;
    return '$imageBaseUrl/$clean';
  }

  /// رابط WebSocket Reverb للإشعارات الفورية (Real-time)
  /// هذا هو الـ App Key من إعدادات Reverb في Laravel (config/reverb.php)
  static const String reverbAppKey = 'zcl7catxuajlvjbringa';

  /// مضيف WebSocket Reverb — يجب أن يكون نفس IP الخادم للاتصال من الجهاز
  static String get reverbHost =>
      kIsWeb ? '127.0.0.1' : '192.168.1.111';

  /// منفذ WebSocket Reverb
  static const int reverbPort = 8080;

  /// استخدام TLS (HTTPS) للاتصال بـ WebSocket
  static const bool reverbUseTLS = false;

  /// رابط مصادقة القنوات الخاصة (Broadcasting Auth)
  /// للـ web يكون المسار نسبي لأن الملفات تُخدم من نفس Laravel
  static String get authEndpoint =>
      kIsWeb ? '/broadcasting/auth' : 'http://192.168.1.111:8000/broadcasting/auth';

}
