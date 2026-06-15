import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../core/widgets/property_card.dart';
import '../../core/widgets/loading_widget.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/notification_bell.dart';
import '../../providers/property_provider.dart';
import '../../providers/booking_provider.dart';
import '../../providers/favorite_provider.dart';
import '../../providers/notification_provider.dart';
import '../../l10n/app_localizations.dart';

/// الشاشة الرئيسية للمستأجر — تعرض العقارات المقترحة وشريط البحث والدردشة والإشعارات
class TenantHomeScreen extends StatefulWidget {
  const TenantHomeScreen({super.key});

  @override
  State<TenantHomeScreen> createState() => _TenantHomeScreenState();
}

/// حالة [TenantHomeScreen] — تحميل العقارات والحجوزات والمفضلات والإشعارات عند التهيئة
class _TenantHomeScreenState extends State<TenantHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PropertyProvider>().loadProperties(refresh: true);
      context.read<BookingProvider>().loadBookings();
      context.read<FavoriteProvider>().loadFavorites();
      context.read<NotificationProvider>().loadUnreadCount();
    });
  }

  /// Builds the tenant home screen with search, property list, and favorites.
  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final propertyProvider = context.watch<PropertyProvider>();
    final fav = context.watch<FavoriteProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final accentColor = isDark ? MaskanColors.kBlueSky : MaskanColors.kBlue;

    return MaskanScaffold(
      noSafeArea: true,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
                      child: Row(
                        children: [
                          SvgPicture.asset('assets/images/house-icon.svg', width: 24, height: 24,
                            colorFilter: ColorFilter.mode(accentColor, BlendMode.srcIn),
                          ),
                          const SizedBox(width: 8),
                          Text(AppConstants.appName, style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold, color: accentColor,
                          )),
                          const Spacer(),
                          IconButton(
                            icon: const Icon(Icons.chat_bubble_outline, color: MaskanColors.kBlue),
                            onPressed: () => context.push(AppRoutes.conversations),
                          ),
                          NotificationBell(iconSize: 22),
                        ],
                      ),
                    ),
                  ),
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 0, 20, 8),
                      child: Text(loc.findProperty,
                        style: TextStyle(fontSize: 14, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                      ),
                    ),
                  ),
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                      child: Container(
                        decoration: BoxDecoration(
                          color: isDark ? MaskanColors.kBgCard2 : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: isDark ? Colors.white12 : Colors.black12),
                        ),
                        child: GestureDetector(
                          onTap: () => context.push(AppRoutes.propertyList),
                          child: Container(
                            height: 44,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            child: Row(
                              children: [
                                Icon(Icons.search, size: 20, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                                const SizedBox(width: 8),
                                Text(loc.searchHint, style: TextStyle(
                                  color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted,
                                  fontSize: 14,
                                )),
                                const Spacer(),
                                Container(
                                  width: 28, height: 28,
                                  decoration: BoxDecoration(
                                    color: MaskanColors.kBlue.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Icon(Icons.tune, size: 16, color: MaskanColors.kBlue),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 4),
                      child: Row(
                        children: [
                          Text(loc.suggestedProperties, style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold, color: accentColor,
                          )),
                          const SizedBox(width: 8),
                          Container(width: 24, height: 3, decoration: BoxDecoration(
                            color: MaskanColors.kGold,
                            borderRadius: BorderRadius.circular(2),
                          )),
                          const Spacer(),
                          GestureDetector(
                            onTap: () => context.go(AppRoutes.propertyList),
                            child: Text(loc.viewAll, style: TextStyle(
                              fontSize: 13, color: MaskanColors.kGold, fontWeight: FontWeight.w600,
                            )),
                          ),
                        ],
                      ),
                    ),
                  ),
                  if (propertyProvider.isLoading && propertyProvider.properties.isEmpty)
                    const SliverFillRemaining(child: LoadingWidget())
                  else
                    SliverList(
                      delegate: SliverChildBuilderDelegate(
                        (context, i) => PropertyCard(
                          property: propertyProvider.properties[i],
                          isFavorite: fav.isFavorite(propertyProvider.properties[i].id),
                          onFavoriteTap: () {
                            fav.toggleFavorite(propertyProvider.properties[i].id).then((ok) {
                              if (!ok && context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                                  content: Text(AppLocalizations.of(context).errorOccurred),
                                  duration: const Duration(seconds: 2),
                                ));
                              }
                            });
                          },
                          onTap: () => context.push(
                            AppRoutes.propertyDetail.replaceAll(':id', '${propertyProvider.properties[i].id}'),
                          ),
                        ),
                        childCount: propertyProvider.properties.length,
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
