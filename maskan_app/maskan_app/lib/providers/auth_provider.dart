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

  /// The currently authenticated user, or null if not logged in.
  User? get user => _user;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;
  /// Whether the user needs to complete OTP verification.
  bool get needsOtp => _needsOtp;
  /// The last error message, or null if no error occurred.
  String? get error => _error;
  /// The phone number awaiting OTP verification.
  String? get pendingPhone => _pendingPhone;
  /// Whether a user is currently logged in.
  bool get isLoggedIn => _user != null;
  /// Whether the logged-in user is a tenant.
  bool get isTenant => _user?.isTenant ?? false;
  /// Whether the logged-in user is a technician.
  bool get isTechnician => _user?.isTechnician ?? false;

  /// Loads the user from local storage and validates the session with the API.
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

  /// Authenticates the user with [phone] and [password]. Returns true on success.
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

  /// Creates a new user account. May require OTP activation depending on the role.
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

  /// Fetches the list of available specialties (for technicians).
  Future<List<Map<String, dynamic>>> getSpecialties() async {
    try {
      final response = await _api.get(ApiEndpoints.specialties);
      final List<dynamic> data = response.data is List ? response.data : (response.data['data'] ?? []);
      return data.cast<Map<String, dynamic>>();
    } catch (_) {
      return [];
    }
  }

  /// The last OTP returned by the server (only in non-production mode).
  String? _lastOtp;
  String? get lastOtp => _lastOtp;

  /// Sends an OTP verification code to the given [phone] number.
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

  /// Confirms the OTP code to activate the account or complete login.
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

  /// Resets the user's password using OTP verification.
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

  /// Logs the user out by clearing the session and local data.
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

  /// Updates the user's profile (name and optional phone number).
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

  /// Uploads a profile photo from the given file [path].
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

  /// Deletes the user's profile photo.
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

  /// Temporarily deactivates the user account.
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

  /// Permanently deletes the user account.
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
