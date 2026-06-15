import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';
import 'package:cookie_jar/cookie_jar.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../config/constants.dart';

/// عميل API المركزي — يتعامل مع جميع طلبات HTTP للخادم الخلفي (Laravel)
///
/// يستخدم [Dio] كمكتبة HTTP مع:
/// - [CookieManager] لإدارة جلسات الدخول عبر الكوكيز (Session-based Auth)
/// - معالجة تلقائية للخطأ 401 (مسح الجلسة + حذف بيانات المستخدم)
/// - ضبط اللغة العربية [Accept-Language: ar] افتراضياً
///
/// يعمل بنمط Singleton (مثيل واحد) عبر [ApiClient._instance].
class ApiClient {
  /// المثيل الوحيد من العميل (Singleton)
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  /// كائن Dio المُستخدم في جميع طلبات HTTP
  late final Dio _dio;
  /// مدير الكوكيز — للحفاظ على جلسة المستخدم
  final CookieJar _cookieJar = CookieJar();

  /// منشئ داخلي خاص — يهيئ [Dio] مع الإعدادات الأساسية
  ApiClient._internal() {
    _dio = Dio(BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: AppConstants.timeout,
      receiveTimeout: AppConstants.timeout,
      headers: {
        'Accept': 'application/json',
        'Accept-Language': 'ar',
      },
    ));

    if (kIsWeb) {
      _dio.options.extra['withCredentials'] = true;
    } else {
      _dio.interceptors.add(CookieManager(_cookieJar));
    }

    // اعتراض الأخطاء — مسح الجلسة عند تلقي 401 (غير مصرح)
    _dio.interceptors.add(InterceptorsWrapper(
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) {
          await _cookieJar.deleteAll();
          final prefs = await SharedPreferences.getInstance();
          await prefs.remove('user_data');
        }
        handler.next(error);
      },
    ));
  }

  /// تغيير لغة الطلبات — يُستخدم عند التبديل بين العربية والإنجليزية
  void setLanguage(String lang) {
    _dio.options.headers['Accept-Language'] = lang;
  }

  /// مسح جميع الكوكيز (تسجيل الخروج)
  Future<void> clearCookies() async {
    await _cookieJar.deleteAll();
  }

  /// طلب GET — جلب بيانات من الخادم
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) =>
      _dio.get(path, queryParameters: queryParameters);

  /// طلب POST — إنشاء مورد جديد (تسجيل، حجز، إلخ)
  Future<Response> post(String path, {dynamic data}) =>
      _dio.post(path, data: data);

  /// طلب PUT — تحديث مورد موجود
  Future<Response> put(String path, {dynamic data}) =>
      _dio.put(path, data: data);

  /// طلب DELETE — حذف مورد
  Future<Response> delete(String path) => _dio.delete(path);

  /// رفع ملفات (صور) مع بيانات إضافية — multipart/form-data
  Future<Response> uploadFiles(String path, List<MapEntry<String, String>> fields, List<MapEntry<String, String>> files) async {
    final formData = FormData();
    for (final field in fields) {
      formData.fields.add(MapEntry(field.key, field.value));
    }
    for (final file in files) {
      if (!kIsWeb) {
        formData.files.add(MapEntry(
          file.key,
          await MultipartFile.fromFile(file.value),
        ));
      }
    }
    return _dio.post(path, data: formData);
  }
}
