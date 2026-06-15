import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/property_card.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/notification_bell.dart';
import '../../providers/property_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة الزائر الرئيسية — عرض العقارات المميزة والكل مع شريط البحث ودعوة لتسجيل الدخول/التسجيل
class VisitorHomeScreen extends StatefulWidget {
  const VisitorHomeScreen({super.key});

  @override
  State<VisitorHomeScreen> createState() => _VisitorHomeScreenState();
}

/// حالة [VisitorHomeScreen] — إدارة تحميل العقارات والبحث
class _VisitorHomeScreenState extends State<VisitorHomeScreen> {
  final _searchController = TextEditingController();

  /// Loads properties and notification count on initialization.
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PropertyProvider>().loadProperties(refresh: true);
    });
  }

  /// Disposes the search controller.
  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  /// Builds the visitor home screen with header, search bar, property lists,
  /// and a bottom call-to-action bar.
  @override
  Widget build(BuildContext context) {
    final provider = context.watch<PropertyProvider>();
    return MaskanScaffold(
      noSafeArea: true,
      body: SafeArea(
        child: provider.isLoading && provider.properties.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : !provider.isLoading && provider.properties.isEmpty
                ? Center(child: Padding(
                    padding: const EdgeInsets.all(32),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.house_outlined, size: 80,
                          color: context.textMuted.withValues(alpha: 0.5)),
                        const SizedBox(height: 16),
                        Text(
                          AppLocalizations.of(context)!.noPropertiesAvailable,
                          style: TextStyle(
                            fontSize: 16, fontFamily: 'Cairo',
                            color: context.textMuted,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (provider.error != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            provider.error!,
                            style: TextStyle(
                              fontSize: 13, fontFamily: 'Cairo',
                              color: MaskanColors.kDanger,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                        const SizedBox(height: 24),
                        PrimaryButton(
                          label: AppLocalizations.of(context)!.retry,
                          height: 44,
                          onPressed: () => provider.loadProperties(refresh: true),
                        ),
                      ],
                    ),
                  ))
                : RefreshIndicator(
                onRefresh: () => provider.loadProperties(refresh: true),
                child: ListView(
                  padding: const EdgeInsets.only(top: 24, bottom: 100),
                  children: [
                    _buildHeader(),
                    const SizedBox(height: 24),
                    _buildSearchBar(),
                    const SizedBox(height: 32),
                    _buildSectionHeader(
                      title: AppLocalizations.of(context)!.featuredProperties,
                      showAll: true,
                      onShowAll: () => context.push(AppRoutes.propertyList),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 260,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: provider.properties.length,
                        itemBuilder: (_, i) {
                          final p = provider.properties[i];
                          return SizedBox(
                            width: 180,
                            child: PropertyCard(
                              property: p,
                              showFavorite: false,
                              onTap: () => context.push(
                                AppRoutes.propertyDetail.replaceFirst(':id', '${p.id}'),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 32),
                    _buildSectionHeader(title: AppLocalizations.of(context)!.allProperties),
                    const SizedBox(height: 12),
                    ...provider.properties.map((p) => PropertyCard(
                      property: p,
                      showFavorite: false,
                      onTap: () => context.push(
                        AppRoutes.propertyDetail.replaceFirst(':id', '${p.id}'),
                      ),
                    )),
                  ],
                ),
              ),
      ),
      bottomNavigationBar: _buildBottomBar(),
    );
  }

  /// Builds the header with a welcome message, app logo, and notification bell.
  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppLocalizations.of(context)!.welcomeBack,
                  style: TextStyle(
                    fontSize: 14,
                    color: context.textMuted,
                    fontFamily: 'Cairo',
                  ),
                ),
                const SizedBox(height: 6),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 48,
                      height: 48,
                      child: SvgPicture.asset(
                        'assets/images/house-icon.svg',
                        colorFilter: const ColorFilter.mode(
                          Color(0xFFC49A2B),
                          BlendMode.srcIn,
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'مسكن',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1A1D22),
                        fontFamily: 'Cairo',
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          NotificationBell(iconColor: context.textPrimary, iconSize: 24),
        ],
      ),
    );
  }

  /// Builds the search bar with a filter button inside a glass card.
  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: MaskanColors.kBlue, width: 1.5),
          boxShadow: [
            BoxShadow(color: MaskanColors.kBlue.withValues(alpha: 0.2), blurRadius: 10, offset: const Offset(0, 3)),
          ],
        ),
        child: GlassCard(
          blurStrength: 8,
          borderRadius: 14,
          height: 52,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Row(
            children: [
              const Icon(Icons.search, color: MaskanColors.kBlueLight, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: TextField(
                  controller: _searchController,
                  style: TextStyle(
                    color: context.textPrimary, fontFamily: 'Cairo', fontSize: 15,
                  ),
                  decoration: InputDecoration(
                    hintText: AppLocalizations.of(context)!.searchHint,
                    hintStyle: TextStyle(color: context.textMuted, fontFamily: 'Cairo'),
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    contentPadding: EdgeInsets.zero,
                  ),
                  onSubmitted: (v) => context.push(AppRoutes.propertyList, extra: v),
                ),
              ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: MaskanColors.kBlueLight.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: MaskanColors.kBlueLight.withValues(alpha: 0.4)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.tune, color: MaskanColors.kBlueLight, size: 16),
                  const SizedBox(width: 4),
                  Text(AppLocalizations.of(context)!.filter, style: TextStyle(
                    color: MaskanColors.kBlueLight, fontSize: 12,
                    fontWeight: FontWeight.w600, fontFamily: 'Cairo',
                  )),
                ],
              ),
            ),
          ],
        ),
      ),
      ),
    );
  }

  /// Builds a section header with an optional "View All" action.
  Widget _buildSectionHeader({
    required String title,
    bool showAll = false,
    VoidCallback? onShowAll,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        children: [
          Text(title, style: const TextStyle(
            fontSize: 18, fontWeight: FontWeight.w600,
            color: MaskanColors.kBlueLight, fontFamily: 'Cairo',
          )),
          const Spacer(),
          if (showAll)
            GestureDetector(
              onTap: onShowAll,
              child: Row(
                children: [
                  Text(AppLocalizations.of(context)!.viewAll, style: TextStyle(
                    fontSize: 13, color: context.textMuted, fontFamily: 'Cairo',
                  )),
                  const SizedBox(width: 2),
                  Icon(Icons.arrow_forward_ios, size: 12, color: context.textMuted),
                ],
              ),
            ),
        ],
      ),
    );
  }

  /// Builds the bottom call-to-action bar with login and create account buttons.
  Widget _buildBottomBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
      decoration: const BoxDecoration(
        color: MaskanColors.kGlassLight,
        border: Border(top: BorderSide(color: MaskanColors.kGlassBorder, width: 0.5)),
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(AppLocalizations.of(context)!.loginToBookMore, style: TextStyle(
              fontSize: 13, color: context.textMuted, fontFamily: 'Cairo',
            )),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: PrimaryButton(
                    label: AppLocalizations.of(context)!.login,
                    height: 44,
                    onPressed: () => context.go(AppRoutes.login),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: MaskanColors.kGold.withValues(alpha: 0.5)),
                    ),
                    child: PrimaryButton(
                      label: AppLocalizations.of(context)!.createAccount,
                      height: 44,
                      backgroundColor: Colors.transparent,
                      foregroundColor: MaskanColors.kGold,
                      onPressed: () => context.go(AppRoutes.register),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
