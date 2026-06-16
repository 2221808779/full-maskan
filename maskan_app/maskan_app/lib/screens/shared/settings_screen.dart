import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_colors.dart';
import '../../core/widgets/glass_card.dart';
import '../../providers/auth_provider.dart';
import '../../providers/theme_provider.dart';
import '../../providers/locale_provider.dart';
import '../../config/routes.dart';
import '../../l10n/app_localizations.dart';

/// شاشة الإعدادات — تبديل الوضع الداكن/الفاتح وتغيير اللغة (عربي/إنجليزي)
/// تعرض أقسام المظهر واللغة والدعم وحول التطبيق
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final localeProvider = context.watch<LocaleProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isAr = localeProvider.isArabic;
    final loc = AppLocalizations.of(context);

    return Scaffold(
      backgroundColor: Colors.transparent,
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Text(loc.settings),
        centerTitle: true,
      ),
      body: Stack(
        children: [
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
                colors: isDark
                    ? [const Color(0xFF0D2A4A), const Color(0xFF061525), const Color(0xFF020B14)]
                    : [const Color(0xFFDEEEF9), const Color(0xFFE8F3FB), const Color(0xFFF5F0E8)],
                stops: const [0.0, 0.5, 1.0],
              ),
            ),
          ),
          SafeArea(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                _sectionHeader(loc.appearance, isDark: isDark),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: GlassCard(
                    borderRadius: 14,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                    child: SwitchListTile(
                      secondary: Icon(
                        isDark ? Icons.dark_mode : Icons.light_mode,
                        color: isDark ? AppColors.blueSky : AppColors.blue,
                      ),
                      title: Text(loc.darkMode,
                        style: TextStyle(
                          color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                          fontFamily: 'Cairo', fontSize: 15,
                        )),
                      subtitle: Text(isDark ? loc.enabled : loc.disabled,
                        style: TextStyle(
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          fontFamily: 'Cairo', fontSize: 13,
                        )),
                      value: isDark,
                      activeThumbColor: AppColors.blue,
                      onChanged: (_) => themeProvider.toggleTheme(),
                    ),
                  ),
                ),
                _sectionHeader(loc.language, isDark: isDark),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: GlassCard(
                    borderRadius: 14,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                    child: Column(
                      children: [
                        ListTile(
                          leading: Icon(Icons.language, color: isDark ? AppColors.blueSky : AppColors.blue),
                          title: Text(loc.arabic,
                            style: TextStyle(
                              color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                              fontFamily: 'Cairo', fontSize: 15,
                            )),
                          trailing: isAr
                              ? const Icon(Icons.check_circle, color: AppColors.blue, size: 20)
                              : Icon(Icons.check_circle_outline,
                                  color: isDark ? AppColors.darkTextMuted : AppColors.lightTextMuted, size: 20),
                          onTap: () {
                            if (!isAr) localeProvider.setLocale(const Locale('ar'));
                          },
                        ),
                        const Divider(height: 0.5, thickness: 0.5, indent: 56),
                        ListTile(
                          leading: Icon(Icons.language, color: isDark ? AppColors.blueSky : AppColors.blue),
                          title: Text(loc.english,
                            style: TextStyle(
                              fontFamily: 'Cairo', fontSize: 15,
                              color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                            )),
                          trailing: !isAr
                              ? const Icon(Icons.check_circle, color: AppColors.blue, size: 20)
                              : Icon(Icons.check_circle_outline,
                                  color: isDark ? AppColors.darkTextMuted : AppColors.lightTextMuted, size: 20),
                          onTap: () {
                            if (isAr) localeProvider.setLocale(const Locale('en'));
                          },
                        ),
                      ],
                    ),
                  ),
                ),
                if (!context.read<AuthProvider>().isTechnician) ...[
                  _sectionHeader(loc.supportSection, isDark: isDark),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: GlassCard(
                      borderRadius: 14,
                      padding: EdgeInsets.zero,
                      child: Column(
                        children: [
                          _menuTile(isDark, Icons.report_problem_outlined, loc.complaints, onTap: () => context.push(AppRoutes.complaintForm)),
                        ],
                      ),
                    ),
                  ),
                ],
                _sectionHeader(loc.aboutSection, isDark: isDark),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: GlassCard(
                    borderRadius: 14,
                    padding: EdgeInsets.zero,
                    child: Column(
                      children: [
                        _menuTile(isDark, Icons.info_outline, loc.appVersion, subtitle: '1.0.0'),
                        const Divider(height: 0.5, thickness: 0.5),
                        _menuTile(isDark, Icons.description_outlined, loc.termsAndConditions, onTap: () => context.push(AppRoutes.terms)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 32),
                Center(
                  child: Text(loc.rightsReserved,
                    style: TextStyle(
                      fontSize: 12,
                      fontFamily: 'Cairo',
                      color: isDark ? AppColors.darkTextMuted : AppColors.lightTextMuted,
                    )),
                ),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ],
      ),
    );
  }


  /// Builds a section header label with styling.
  Widget _sectionHeader(String title, {bool isDark = true}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 8),
      child: Text(title, style: TextStyle(
        fontSize: 13, fontWeight: FontWeight.w600,
        color: isDark ? AppColors.blueSky : AppColors.blueLight,
        fontFamily: 'Cairo',
      )),
    );
  }

  /// Builds a menu list tile with icon, title, optional subtitle, and trailing chevron.
  Widget _menuTile(bool isDark, IconData icon, String title, {String? subtitle, VoidCallback? onTap}) {
    return ListTile(
      leading: Icon(icon, size: 18, color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary),
      title: Text(title, style: TextStyle(
        fontSize: 15,
        fontFamily: 'Cairo',
        color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
      )),
      trailing: subtitle != null
          ? Text(subtitle, style: TextStyle(
              fontSize: 13,
              fontFamily: 'Cairo',
              color: isDark ? AppColors.darkTextMuted : AppColors.lightTextMuted,
            ))
          : Icon(Icons.chevron_left,
              color: isDark ? AppColors.darkTextMuted : AppColors.lightTextMuted, size: 20),
      onTap: onTap,
    );
  }
}
