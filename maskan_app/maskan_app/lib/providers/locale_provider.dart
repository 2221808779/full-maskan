import 'package:flutter/material.dart';
import '../core/services/storage_service.dart';

/// يدير حالة اللغة في التطبيق
/// يحفظ اللغة المختارة عبر [StorageService] ويوفر طرقاً لتحميل وتعيين
/// والتبديل بين العربية والإنجليزية
class LocaleProvider extends ChangeNotifier {
  Locale _locale = const Locale('ar');
  final StorageService _storage = StorageService();
  bool _loaded = false;

  /// اللغة الحالية.
  Locale get locale => _locale;
  /// ما إذا كانت اللغة الحالية هي العربية.
  bool get isArabic => _locale.languageCode == 'ar';
  /// ما إذا تم تحميل اللغة من التخزين.
  bool get loaded => _loaded;

  /// يحمّل اللغة المحفوظة من التخزين المحلي.
  Future<void> loadLocale() async {
    final saved = await _storage.getLocale();
    _locale = saved == 'en' ? const Locale('en') : const Locale('ar');
    _loaded = true;
    notifyListeners();
  }

  /// يعيّن لغة جديدة [locale] ويحفظ الاختيار.
  Future<void> setLocale(Locale locale) async {
    _locale = locale;
    await _storage.saveLocale(locale.languageCode);
    notifyListeners();
  }

  /// يبدّل بين العربية والإنجليزية.
  void toggleLanguage() {
    setLocale(isArabic ? const Locale('en') : const Locale('ar'));
  }
}
