import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/loading_widget.dart';
import '../../core/widgets/empty_state.dart';
import '../../providers/favorite_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض العقارات المفضلة للمستأجر — تحميل المفضلات والتنقل إلى التفاصيل أو إزالتها
class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

/// منطق حالة [FavoritesScreen] — إدارة تحميل وعرض والتنقل بين العقارات المفضلة
class _FavoritesScreenState extends State<FavoritesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoriteProvider>().loadFavorites();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<FavoriteProvider>();
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.myFavorites),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: provider.isLoading
          ? const LoadingWidget()
          : provider.favorites.isEmpty
              ? EmptyState(icon: Icons.favorite_border, title: AppLocalizations.of(context)!.noFavoriteProperties,
                  subtitle: AppLocalizations.of(context)!.addFavoritesToFollow,
                  actionLabel: AppLocalizations.of(context)!.browseProperties,
                  onAction: _navigateToProperties,
                )
              : RefreshIndicator(
                  onRefresh: () => provider.loadFavorites(),
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: provider.favorites.length,
                    itemBuilder: (_, i) {
                      final fav = provider.favorites[i];
                      return GlassCard(
                        padding: EdgeInsets.zero,
                        child: ListTile(
                          leading: ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              width: 60, height: 60,
                              color: MaskanColors.kBgCard,
                              child: const Icon(Icons.home, color: Colors.grey),
                            ),
                          ),
                          title: Text(fav.propertyTitle ?? 'عقار #${fav.propertyId}'),
                          subtitle: Text(fav.propertyAddress ?? ''),
                          trailing: IconButton(
                            icon: const Icon(Icons.favorite, color: Colors.red),
                            onPressed: () => provider.toggleFavorite(fav.propertyId),
                          ),
                          onTap: () => context.push(
                            AppRoutes.propertyDetail.replaceAll(':id', '${fav.propertyId}'),
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }

  /// ينتقل إلى شاشة قائمة العقارات عند عدم وجود مفضّلات.
  void _navigateToProperties() => context.push(AppRoutes.propertyList);
}
