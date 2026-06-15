import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'config/routes.dart';
import 'core/theme/app_theme.dart';
import 'providers/theme_provider.dart';
import 'providers/locale_provider.dart';
import 'l10n/app_localizations.dart';

/// سلوك تمرير يمنع تأثير التمرير الزائد (Overscroll) على جميع المنصات
class NoOverscrollBehavior extends ScrollBehavior {
  const NoOverscrollBehavior();

  /// إخفاء مؤشر التمرير الزائد
  @override
  Widget buildOverscrollIndicator(BuildContext context, Widget child, ScrollableDetails details) {
    return child;
  }

  /// استخدام ClampingScrollPhysics لمنع التمرير الزائد في iOS
  @override
  ScrollPhysics getScrollPhysics(BuildContext context) {
    return const ClampingScrollPhysics();
  }
}

/// تطبيق مسكن — الجذر الرئيسي مع التوجيه (GoRouter) والثيم (Theme) والترجمة
class MaskanApp extends StatelessWidget {
  const MaskanApp({super.key});

  /// بناء شجرة الويدجت — اختيار الثيم واللغة والتوجيه
  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final localeProvider = context.watch<LocaleProvider>();
    if (!localeProvider.loaded) {
      return const MaterialApp(
        debugShowCheckedModeBanner: false,
        home: Scaffold(body: Center(child: CircularProgressIndicator())),
      );
    }
    return MaterialApp.router(
      key: ValueKey(localeProvider.locale.languageCode),
      title: 'مسكن',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      darkTheme: AppTheme.dark(),
      themeMode: themeProvider.isDark ? ThemeMode.dark : ThemeMode.light,
      routerConfig: AppRoutes.router,
      locale: localeProvider.locale,
      supportedLocales: const [Locale('ar'), Locale('en')],
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      localeResolutionCallback: (locale, supportedLocales) {
        if (locale == null) return const Locale('ar');
        for (final supported in supportedLocales) {
          if (supported.languageCode == locale.languageCode) return supported;
        }
        return const Locale('ar');
      },
      builder: (context, child) {
        return ScrollConfiguration(
          behavior: const NoOverscrollBehavior(),
          child: child!,
        );
      },
    );
  }
}
