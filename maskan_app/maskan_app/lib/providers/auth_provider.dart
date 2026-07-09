import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../core/services/storage_service.dart';
import '../core/services/notification_service.dart';
import '../models/user.dart';

/// يدير حالة المصادقة — تسجيل الدخول، إنشاء حساب، التحقق من OTP، وتسجيل الخروج
///
/// يستخدم [ApiClient] (Dio + CookieManager) للتواصل مع خادم Laravel
/// ويحفظ بيانات المستخدم محلياً عبر [StorageService] (SharedPreferences)
///
/// العمليات الرئيسية:
/// - [login] مصادقة المستخدم وحفظ الجلسة والاشتراك في الإشعارات
/// - [register] إنشاء حساب جديد (قد يتطلب تفعيل OTP)
/// - [verifyOtp] تأكيد رمز OTP لتفعيل الحساب أو إكمال تسجيل الدخول
/// - [logout] مسح الجلسة والبيانات المحلية
class AuthProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();
  final StorageService _storage = StorageService();

  User? _user;
  bool _isLoading = false;
  bool _needsOtp = false;
  String? _error;
  String? _pendingPhone;

  /// المستخدم المُصدَّق حالياً، أو null إذا لم يسجل الدخول.
  User? get user => _user;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;
  /// ما إذا كان المستخدم بحاجة لإكمال التحقق عبر OTP.
  bool get needsOtp => _needsOtp;
  /// آخر رسالة خطأ، أو null إذا لم يحدث خطأ.
  String? get error => _error;
  /// رقم الهاتف بانتظار التحقق عبر OTP.
  String? get pendingPhone => _pendingPhone;
  /// ما إذا كان المستخدم مسجل الدخول حالياً.
  bool get isLoggedIn => _user != null;
  /// ما إذا كان المستخدم المسجل مستأجراً.
  bool get isTenant => _user?.isTenant ?? false;
  /// ما إذا كان المستخدم المسجل فنياً.
  bool get isTechnician => _user?.isTechnician ?? false;

  /// يحمّل المستخدم من التخزين المحلي ويتحقق من صحة الجلسة مع API.
  Future<void> loadUser() async {
    _user = await _storage.getUser();
    notifyListeners();
    if (_user == null) return;
    try {
      final response = await _api.get(ApiEndpoints.profile);
      _user = User.fromJson(response.data['user'] ?? {});
      await _storage.saveUser(_user!);
    } catch (_) {}
    notifyListeners();
  }

  /// يُوثّق المستخدم باستخدام [phone] و [password]. يُرجِع true عند النجاح.
  Future<bool> login(String phone, String password) async {
    _isLoading = true;
    _error = null;
    _needsOtp = false;
    _pendingPhone = null;
    notifyListeners();
    try {
      final response = await _api.post(ApiEndpoints.login, data: {
        'phone': phone,
        'password': password,
      });
      _user = User.fromJson(response.data['user'] ?? {});
      await _storage.saveUser(_user!);
      await NotificationService().subscribeToUserChannel(_user!.id);
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      if (e.response?.statusCode == 403) {
        final data = e.response?.data;
        if (data is Map && data['needs_otp'] == true) {
          _needsOtp = true;
          _pendingPhone = data['phone'] ?? phone;
        }
        _error = data?['message'] ?? 'رقم الهاتف أو كلمة المرور غير صحيحة';
      } else if (e.response?.statusCode == 422) {
        final errors = e.response?.data?['errors'];
        if (errors is Map) {
          _error = (errors.values.first as List).first.toString();
        } else {
          _error = 'رقم الهاتف أو كلمة المرور غير صحيحة';
        }
      } else if (e.response == null) {
        _error = 'تعذر الاتصال بالخادم، تأكد من اتصالك بالإنترنت';
      } else {
        _error = 'رقم الهاتف أو كلمة المرور غير صحيحة';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'حدث خطأ، حاول مرة أخرى';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// ينشئ حساب مستخدم جديد. قد يتطلب تفعيل OTP حسب الدور.
  Future<bool> register({
    required String name,
    required String phone,
    required String password,
    required String userType,
    List<int>? specializations,
    int? experienceYears,
  }) async {
    _isLoading = true;
    _error = null;
    _needsOtp = false;
    notifyListeners();
    try {
      final body = <String, dynamic>{
        'full_name': name,
        'phone': phone,
        'password': password,
        'password_confirmation': password,
        'role': userType,
      };
      if (specializations != null && specializations.isNotEmpty) {
        body['specializations'] = specializations;
      }
      if (experienceYears != null) {
        body['experience_years'] = experienceYears;
      }
      final response = await _api.post(ApiEndpoints.register, data: body);
      final data = response.data['data'] ?? response.data;
      _user = User.fromJson(data['user'] ?? {});
      await _storage.saveUser(_user!);
      if (data['requires_otp'] == true) {
        _needsOtp = true;
        _pendingPhone = phone;
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        final errors = e.response?.data?['errors'];
        if (errors is Map) {
          _error = (errors.values.first as List).first.toString();
        } else {
          _error = 'فشل إنشاء الحساب';
        }
      } else {
        _error = 'فشل إنشاء الحساب';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'حدث خطأ، حاول مرة أخرى';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يجلب قائمة التخصصات المتاحة (للفنيين).
  Future<List<Map<String, dynamic>>> getSpecialties() async {
    try {
      final response = await _api.get(ApiEndpoints.specialties);
      final List<dynamic> data = response.data is List ? response.data : (response.data['data'] ?? []);
      return data.cast<Map<String, dynamic>>();
    } catch (_) {
      return [];
    }
  }

  /// آخر OTP أعاده الخادم (فقط في وضع غير الإنتاج).
  String? _lastOtp;
  String? get lastOtp => _lastOtp;

  /// يرسل رمز التحقق OTP إلى رقم [phone] المحدد.
  Future<bool> sendOtp(String phone) async {
    _isLoading = true;
    _error = null;
    _lastOtp = null;
    notifyListeners();
    try {
      final response = await _api.post(ApiEndpoints.sendOtp, data: {'phone': phone});
      final data = response.data;
      if (data is Map && data.containsKey('otp')) {
        _lastOtp = data['otp'].toString();
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      if (e.type == DioExceptionType.connectionTimeout || e.type == DioExceptionType.receiveTimeout) {
        _error = 'تعذر الاتصال بالخادم، تأكد من تشغيل السيرفر';
      } else if (e.type == DioExceptionType.connectionError) {
        _error = 'الخادم غير متاح، تحقق من عنوان IP';
      } else if (e.response?.statusCode == 422) {
        final errors = e.response?.data?['errors'];
        if (errors is Map) {
          _error = (errors.values.first as List).first.toString();
        } else {
          _error = 'بيانات غير صحيحة';
        }
      } else {
        _error = 'فشل إرسال رمز التحقق (${e.response?.statusCode ?? e.type.name})';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'فشل إرسال رمز التحقق';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يؤكّد رمز OTP لتفعيل الحساب أو إكمال تسجيل الدخول.
  Future<bool> verifyOtp(String phone, String otp) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      await _api.post(ApiEndpoints.verifyOtp, data: {
        'phone': phone,
        'otp': otp,
      });
      _needsOtp = false;
      _pendingPhone = null;
      try {
        final profileResponse = await _api.get(ApiEndpoints.profile);
        _user = User.fromJson(profileResponse.data['user'] ?? {});
        await _storage.saveUser(_user!);
      } catch (_) {}
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = 'رمز التحقق غير صحيح';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يعيد تعيين كلمة مرور المستخدم باستخدام التحقق عبر OTP.
  Future<bool> resetPassword(String phone, String otp, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      await _api.post(ApiEndpoints.resetPassword, data: {
        'phone': phone,
        'otp': otp,
        'password': password,
        'password_confirmation': password,
      });
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = 'فشل إعادة تعيين كلمة المرور';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يسجّل خروج المستخدم بمسح الجلسة والبيانات المحلية.
  Future<void> logout() async {
    try {
      await _api.post(ApiEndpoints.logout);
    } catch (_) {}
    await _storage.clearAll();
    _user = null;
    _needsOtp = false;
    _pendingPhone = null;
    notifyListeners();
  }

  /// يحدّث الملف الشخصي للمستخدم (الاسم ورقم الهاتف اختيارياً).
  Future<bool> updateProfile(String name, String? phone) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = <String, dynamic>{'full_name': name};
      if (phone != null) data['phone'] = phone;
      final response = await _api.put(ApiEndpoints.updateProfile, data: data);
      _user = User.fromJson(response.data['user'] ?? response.data['data'] ?? {});
      await _storage.saveUser(_user!);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = 'فشل تحديث الملف الشخصي';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يرفع صورة شخصية من مسار الملف [path] المحدد.
  Future<bool> uploadPhoto(String path) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final response = await _api.uploadFiles(
        ApiEndpoints.uploadPhoto,
        [],
        [MapEntry('profile_image', path)],
      );
      _user = User.fromJson(response.data['user'] ?? response.data['data'] ?? {});
      await _storage.saveUser(_user!);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = 'فشل رفع الصورة';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يحذف الصورة الشخصية للمستخدم.
  Future<bool> deletePhoto() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      await _api.delete(ApiEndpoints.uploadPhoto);
      _user = _user?.copyWith(profileImage: null);
      if (_user != null) await _storage.saveUser(_user!);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = 'فشل حذف الصورة';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// يُعطّل حساب المستخدم بشكل مؤقت.
  Future<bool> deactivateAccount() async {
    try {
      await _api.post(ApiEndpoints.deactivateAccount);
      await logout();
      return true;
    } catch (e) {
      _error = 'فشل إلغاء الحساب';
      notifyListeners();
      return false;
    }
  }

  /// يحذف حساب المستخدم نهائياً.
  Future<bool> deleteAccount() async {
    try {
      await _api.post(ApiEndpoints.deleteAccount);
      await logout();
      return true;
    } catch (e) {
      _error = 'فشل حذف الحساب';
      notifyListeners();
      return false;
    }
  }
}
