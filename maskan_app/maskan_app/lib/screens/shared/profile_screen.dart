import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../providers/favorite_provider.dart';
import '../../providers/booking_provider.dart';
import '../../providers/review_provider.dart';
import '../../core/widgets/glass_container.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../l10n/app_localizations.dart';

/// شاشة الملف الشخصي — معلومات المستخدم والإحصائيات (الحجوزات والمفضلات والتقييمات)
/// خيارات إدارة الحساب والتنقل إلى الإعدادات.
///
/// تكييف المحتوى حسب نوع المستخدم (مستأجر أو فني)
class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

/// منطق حالة [ProfileScreen] — تحميل بيانات الملف الشخصي
/// إجراءات الحساب (إلغاء التنشيط، حذف، تسجيل خروج)، وبناء مجموعات القوائم.
class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      context.read<FavoriteProvider>().loadFavorites();
      context.read<BookingProvider>().loadBookings();
      if (auth.isTechnician && auth.user != null) {
        context.read<ReviewProvider>().loadTechnicianReviews(auth.user!.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final loc = AppLocalizations.of(context);
    final favCount = context.watch<FavoriteProvider>().favorites.length;
    final bookingCount = context.watch<BookingProvider>().bookings.length;

    return MaskanScaffold(
      appBar: AppBar(
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Text(loc.profile, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 32),
              decoration: const BoxDecoration(color: MaskanColors.kBgCard),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: MaskanColors.kBlue,
                    backgroundImage: (user?.profileImage != null)
                        ? NetworkImage(AppConstants.resolveImageUrl(user!.profileImage!))
                        : null,
                    child: (user?.profileImage == null)
                        ? Text(
                            (user?.fullName ?? '?')[0],
                            style: const TextStyle(fontSize: 32, color: Colors.white, fontFamily: 'Cairo'),
                          )
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(user?.fullName ?? '', style: const TextStyle(
                    fontSize: 22, fontWeight: FontWeight.bold,
                    color: Colors.white, fontFamily: 'Cairo',
                  )),
                  const SizedBox(height: 8),
                  GoldBadge(label: user?.userType == 'technician' ? loc.technician : loc.tenant),
                  const SizedBox(height: 8),
                  TextButton.icon(
                    onPressed: () => context.push(AppRoutes.editProfile),
                    icon: const Icon(Icons.edit, size: 14, color: Colors.white70),
                    label: Text(loc.editProfile, style: const TextStyle(
                      fontSize: 13, color: Colors.white70, fontFamily: 'Cairo',
                    )),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Row(
                children: [
                  if (auth.isTechnician) ...[
                    GestureDetector(
                      onTap: () => context.push(AppRoutes.technicianReviews),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        decoration: BoxDecoration(color: MaskanColors.kBgCard, borderRadius: BorderRadius.circular(12)),
                        child: Text('${loc.myStatsReviews}: ${context.watch<ReviewProvider>().technicianReviews.length}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: Colors.white, fontFamily: 'Cairo')),
                      ),
                    ),
                  ] else ...[
                    Expanded(child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(color: MaskanColors.kBgCard, borderRadius: BorderRadius.circular(12)),
                      child: Text('${loc.myStatsBookings}: $bookingCount', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: Colors.white, fontFamily: 'Cairo')),
                    )),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: () => context.push(AppRoutes.favorites),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        decoration: BoxDecoration(color: MaskanColors.kBgCard, borderRadius: BorderRadius.circular(12)),
                        child: Text('${loc.myStatsFavorites}: $favCount', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: Colors.white, fontFamily: 'Cairo')),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 24),
            _buildMenuGroup(context, loc.accountSection, [
              _MenuItemData(Icons.edit_outlined, loc.editProfile, () => context.push(AppRoutes.editProfile)),
              if (!auth.isTechnician)
                _MenuItemData(Icons.favorite_outline, loc.myStatsFavorites, () => context.push(AppRoutes.favorites)),
              _MenuItemData(Icons.notifications_outlined, loc.notifications, () => context.push(AppRoutes.notifications)),
              _MenuItemData(Icons.settings_outlined, loc.settings, () => context.push(AppRoutes.settings)),
            ]),
            const SizedBox(height: 16),
            _buildMenuGroup(context, loc.advancedSection, [
              _MenuItemData(Icons.pause_circle_outline, loc.deactivateAccount, () => _showDeactivateDialog(context),
                color: MaskanColors.kWarning),
              _MenuItemData(Icons.delete_outline, loc.deleteAccount, () => _showDeleteDialog(context),
                color: MaskanColors.kDanger),
              _MenuItemData(Icons.logout, loc.logout, () => _logout(context),
                color: MaskanColors.kDanger),
            ]),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  /// يُنشئ قسم قائمة مجمّع داخل [GlassCard] بعنوان وعناصر قابلة للنقر.
  Widget _buildMenuGroup(BuildContext context, String title, List<_MenuItemData> items) {
    return GlassCard(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
            child: Text(title, style: TextStyle(
              fontSize: 12, color: context.textMuted, fontFamily: 'Cairo',
            )),
          ),
          ...List.generate(items.length, (i) {
            return Column(
              children: [
                if (i > 0) const Divider(height: 0.5, color: MaskanColors.kGlassBorder),
                SizedBox(
                  height: 56,
                  child: ListTile(
                    leading: Icon(items[i].icon, size: 18, color: items[i].color ?? context.textSecondary),
                    title: Text(items[i].label, style: TextStyle(
                      fontSize: 15, fontFamily: 'Cairo',
                      color: items[i].color ?? context.textPrimary,
                    )),
                    trailing: Icon(Icons.chevron_left, color: context.textMuted, size: 20),
                    onTap: items[i].onTap,
                  ),
                ),
              ],
            );
          }),
        ],
      ),
    );
  }

  /// يعرض مربع حوار تأكيد لإلغاء تنشيط الحساب.
  void _showDeactivateDialog(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: isDark ? MaskanColors.kBgCard : MaskanColors.lBgCard,
        title: Text(loc.deactivateAccount, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        content: Text(loc.deactivateDesc,
          style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text(loc.back,
            style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo'))),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.read<AuthProvider>().deactivateAccount();
            },
            child: Text(loc.confirm, style: const TextStyle(color: MaskanColors.kWarning, fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
  }

  /// يعرض مربع حوار تأكيد لحذف الحساب نهائياً.
  void _showDeleteDialog(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: isDark ? MaskanColors.kBgCard : MaskanColors.lBgCard,
        title: Text(loc.deleteAccount, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        content: Text(loc.deleteAccountDesc,
          style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text(loc.back,
            style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo'))),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.read<AuthProvider>().deleteAccount();
            },
            child: Text(loc.confirmDelete, style: const TextStyle(color: MaskanColors.kDanger, fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
  }

  /// يعرض مربع حوار تأكيد لتسجيل خروج المستخدم الحالي.
  void _logout(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: isDark ? MaskanColors.kBgCard : MaskanColors.lBgCard,
        title: Text(loc.logout, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        content: Text(loc.logoutConfirm,
          style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: Text(loc.cancel,
            style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo'))),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.read<AuthProvider>().logout();
              context.go(AppRoutes.login);
            },
            child: Text(loc.logout, style: const TextStyle(color: MaskanColors.kDanger, fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
  }
}

/// فئة بيانات داخلية تمثل عنصر قائمة واحد في قوائم الملف الشخصي
class _MenuItemData {
  /// الأيقونة المعروضة على يسار عنصر القائمة.
  final IconData icon;

  /// نص التسمية لعنصر القائمة.
  final String label;

  /// رد اتصال اختياري يُستدعى عند النقر على العنصر.
  final VoidCallback? onTap;

  /// لون تلوين اختياري للأيقونة والتسمية.
  final Color? color;

  const _MenuItemData(this.icon, this.label, this.onTap, {this.color});
}
