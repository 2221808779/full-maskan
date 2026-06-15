import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../../models/user.dart';

/// خدمة التخزين المحلي — تدير حفظ واسترجاع البيانات باستخدام SharedPreferences
/// مثل التوكن، المستخدم، الثيم، اللغة
class StorageService {
  static final StorageService _instance = StorageService._internal();
  factory StorageService() => _instance;
  StorageService._internal();

  /// الحصول على مثيل SharedPreferences بشكل غير متزامن
  /// الحصول على مثيل SharedPreferences بشكل غير متزامن
  Future<SharedPreferences> get _prefs => SharedPreferences.getInstance();

  /// حفظ توكن المصادقة في التخزين المحلي
  Future<void> saveToken(String token) async {
    final p = await _prefs;
    await p.setString('auth_token', token);
  }

  /// استرجاع توكن المصادقة من التخزين المحلي
  /// تعيد null إذا لم يكن موجوداً
  Future<String?> getToken() async {
    final p = await _prefs;
    return p.getString('auth_token');
  }

  /// حذف توكن المصادقة من التخزين المحلي
  Future<void> deleteToken() async {
    final p = await _prefs;
    await p.remove('auth_token');
  }

  /// حفظ بيانات المستخدم في التخزين المحلي بصيغة JSON
  Future<void> saveUser(User user) async {
    final p = await _prefs;
    await p.setString('user_data', jsonEncode(user.toJson()));
  }

  /// استرجاع بيانات المستخدم من التخزين المحلي
  /// تعيد null إذا لم يكن موجوداً
  Future<User?> getUser() async {
    final p = await _prefs;
    final data = p.getString('user_data');
    if (data == null) return null;
    return User.fromJson(jsonDecode(data));
  }

  /// حذف بيانات المستخدم من التخزين المحلي
  Future<void> deleteUser() async {
    final p = await _prefs;
    await p.remove('user_data');
  }

  /// حفظ وضع الثيم (فاتح/داكن) في التخزين المحلي
  Future<void> saveThemeMode(String mode) async {
    final p = await _prefs;
    await p.setString('theme_mode', mode);
  }

  /// استرجاع وضع الثيم من التخزين المحلي
  /// تعيد null إذا لم يكن محفوظاً
  Future<String?> getThemeMode() async {
    final p = await _prefs;
    return p.getString('theme_mode');
  }

  /// حفظ إعدادات اللغة في التخزين المحلي
  Future<void> saveLocale(String locale) async {
    final p = await _prefs;
    await p.setString('locale', locale);
  }

  /// استرجاع إعدادات اللغة من التخزين المحلي
  /// تعيد null إذا لم تكن محفوظة
  Future<String?> getLocale() async {
    final p = await _prefs;
    return p.getString('locale');
  }

  /// مسح جميع البيانات المخزنة محلياً
  Future<void> clearAll() async {
    final p = await _prefs;
    await p.clear();
  }
}
