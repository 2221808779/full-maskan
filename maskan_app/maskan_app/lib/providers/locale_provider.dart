import 'package:flutter/material.dart';
import '../core/services/storage_service.dart';

/// يدير حالة اللغة في التطبيق
/// يحفظ اللغة المختارة عبر [StorageService] ويوفر طرقاً لتحميل وتعيين
/// والتبديل بين العربية والإنجليزية
class LocaleProvider extends ChangeNotifier {
  Locale _locale = const Locale('ar');
  final StorageService _storage = StorageService();
  bool _loaded = false;

  /// The current locale.
  Locale get locale => _locale;
  /// Whether the current locale is Arabic.
  bool get isArabic => _locale.languageCode == 'ar';
  /// Whether the locale has been loaded from storage.
  bool get loaded => _loaded;

  /// Loads the saved locale from local storage.
  Future<void> loadLocale() async {
    final saved = await _storage.getLocale();
    _locale = saved == 'en' ? const Locale('en') : const Locale('ar');
    _loaded = true;
    notifyListeners();
  }

  /// Sets a new [locale] and persists the choice.
  Future<void> setLocale(Locale locale) async {
    _locale = locale;
    await _storage.saveLocale(locale.languageCode);
    notifyListeners();
  }

  /// Toggles between Arabic and English.
  void toggleLanguage() {
    setLocale(isArabic ? const Locale('en') : const Locale('ar'));
  }
}
