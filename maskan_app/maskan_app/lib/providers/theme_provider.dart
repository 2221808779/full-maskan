import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/services/storage_service.dart';
import '../core/theme/app_theme.dart';

/// يدير حالة الثيم (فاتح/داكن) في التطبيق
/// يحفظ الاختيار محلياً ويطبق إعدادات شريط النظام
class ThemeProvider extends ChangeNotifier {
  bool _isDark = false;
  final StorageService _storage = StorageService();

  ThemeData get theme => _isDark ? AppTheme.dark() : AppTheme.light();
  bool get isDark => _isDark;

  Future<void> loadTheme() async {
    final mode = await _storage.getThemeMode();
    _isDark = mode == 'dark';
    _applySystemUi();
    notifyListeners();
  }

  Future<void> toggleTheme() async {
    _isDark = !_isDark;
    _applySystemUi();
    await _storage.saveThemeMode(_isDark ? 'dark' : 'light');
    notifyListeners();
  }

  Future<void> setDark(bool value) async {
    _isDark = value;
    _applySystemUi();
    await _storage.saveThemeMode(_isDark ? 'dark' : 'light');
    notifyListeners();
  }

  void _applySystemUi() {
    SystemChrome.setSystemUIOverlayStyle(SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: _isDark ? Brightness.light : Brightness.dark,
      systemNavigationBarColor: _isDark ? const Color(0xFF030810) : const Color(0xFF1B3A52),
      systemNavigationBarIconBrightness:
          _isDark ? Brightness.light : Brightness.light,
      systemNavigationBarDividerColor: Colors.transparent,
    ));
  }
}
