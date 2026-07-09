import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:share_plus/share_plus.dart';
import '../../config/constants.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/glass_container.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../providers/property_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/favorite_provider.dart';
import '../../providers/review_provider.dart';
import '../../models/review.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تفاصيل العقار الكاملة — معرض صور ووصف ومواصفات ووسائل راحة ومعلومات المالك والتقييمات والخريطة
class PropertyDetailScreen extends StatefulWidget {
  /// معرّف العقار المراد عرضه
  final int propertyId;
  const PropertyDetailScreen({super.key, required this.propertyId});

  @override
  State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

/// حالة [PropertyDetailScreen] — إدارة معرض الصور والوصف
  /// التوسّع، وحوارات تسجيل الدخول المطلوبة.
class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  bool _showFullDescription = false;
  int _imagePageIndex = 0;

  /// يُظهر حواراً يطلب من المستخدم تسجيل الدخول لإجراء معيّن.
  void _requireLogin({String? action, bool goBackOnCancel = false}) {
    if (context.read<AuthProvider>().isLoggedIn) return;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final loc = AppLocalizations.of(context);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1A2A3A) : MaskanColors.lBg,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(loc.loginRequired, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        content: Text(action != null
            ? '${loc.loginRequiredFor}$action'
            : loc.featureRequiresLogin,
            style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              if (goBackOnCancel) context.pop();
            },
            child: Text(loc.cancel, style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
          ),
          PrimaryButton(
            label: loc.login,
            height: 44,
            onPressed: () {
              Navigator.pop(ctx);
              context.go(AppRoutes.login);
            },
          ),
        ],
      ),
    );
  }

  /// يحمّل تفاصيل العقار والتقييمات وحالة المفضّلة عند التهيئة.
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!context.read<AuthProvider>().isLoggedIn) {
        _requireLogin(goBackOnCancel: true);
        return;
      }
      context.read<PropertyProvider>().loadPropertyDetail(widget.propertyId);
      context.read<ReviewProvider>().loadPropertyReviews(widget.propertyId);
      if (context.read<AuthProvider>().isLoggedIn) {
        context.read<FavoriteProvider>().check(widget.propertyId);
      }
    });
  }

  /// يُرجع تسمية محلّية لنوع العقار المُعطى.
  String _typeLabel(BuildContext context, String? type) {
    final loc = AppLocalizations.of(context);
    switch (type) {
      case 'apartment': return loc.apartment;
      case 'villa': return loc.villa;
      case 'studio': return loc.studio;
      case 'shop': return loc.shop;
      case 'office': return loc.office;
      case 'warehouse': return loc.warehouse;
      case 'land': return loc.land;
      default: return type ?? loc.property;
    }
  }

  /// يبني شاشة تفاصيل العقار الكاملة مع معرض الصور والتفاصيل
  /// والخريطة والتقييمات ومعلومات المالك وشريط الإجراءات السفلي.
  @override
  Widget build(BuildContext context) {
    final provider = context.watch<PropertyProvider>();
    final auth = context.watch<AuthProvider>();
    final fav = context.watch<FavoriteProvider>();
    final reviewProvider = context.watch<ReviewProvider>();
    final property = provider.selectedProperty;
    final screenHeight = MediaQuery.of(context).size.height;
    final topPadding = MediaQuery.of(context).padding.top;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textPrimary = context.textPrimary;
    final textSecondary = context.textSecondary;
    final textMuted = context.textMuted;
    final chipBg = isDark ? const Color(0x1AFFFFFF) : const Color(0x1A2D5F8A);

    return MaskanScaffold(
      body: provider.isLoading && property == null
          ? const Center(child: CircularProgressIndicator())
          : property == null
              ? Center(child: Text(AppLocalizations.of(context).propertyNotFound, style: TextStyle(
                  color: textMuted, fontFamily: 'Cairo',
                )))
              : CustomScrollView(
                  slivers: [
                    SliverToBoxAdapter(
                      child: SizedBox(
                        height: screenHeight * 0.55,
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            property.images.isNotEmpty
                                ? GestureDetector(
                                    onHorizontalDragEnd: (details) {
                                      if (property.images.length <= 1) return;
                                      if (details.primaryVelocity! < -50 && _imagePageIndex < property.images.length - 1) {
                                        setState(() => _imagePageIndex++);
                                      } else if (details.primaryVelocity! > 50 && _imagePageIndex > 0) {
                                        setState(() => _imagePageIndex--);
                                      }
                                    },
                                    child: AnimatedSwitcher(
                                      duration: const Duration(milliseconds: 300),
                                      child: CachedNetworkImage(
                                        key: ValueKey(_imagePageIndex),
                                        imageUrl: AppConstants.resolveImageUrl(property.images[_imagePageIndex]),
                                        fit: BoxFit.cover,
                                        width: double.infinity,
                                        placeholder: (_, _) => Container(color: isDark ? const Color(0xFF1A2A3A) : const Color(0xFFE8EFF5)),
                                        errorWidget: (_, _, _) => Container(
                                          color: isDark ? const Color(0xFF1A2A3A) : const Color(0xFFE8EFF5),
                                          child: Icon(Icons.broken_image, color: textMuted),
                                        ),
                                      ),
                                    ),
                                  )
                                : Container(
                                    color: isDark ? const Color(0xFF1A2A3A) : const Color(0xFFE8EFF5),
                                    child: Icon(Icons.home, size: 80, color: textMuted),
                                  ),
                            Container(
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.bottomCenter,
                                  end: Alignment.topCenter,
                                  colors: [Colors.black54, Colors.transparent],
                                  stops: [0.0, 0.6],
                                ),
                              ),
                            ),
                            if (property.images.length > 1)
                              Positioned(
                                bottom: 16,
                                left: 0,
                                right: 0,
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: List.generate(property.images.length, (i) => GestureDetector(
                                    onTap: () => setState(() => _imagePageIndex = i),
                                    child: AnimatedContainer(
                                      duration: const Duration(milliseconds: 200),
                                      margin: const EdgeInsets.symmetric(horizontal: 3),
                                      width: _imagePageIndex == i ? 20 : 6,
                                      height: 6,
                                      decoration: BoxDecoration(
                                        color: _imagePageIndex == i ? MaskanColors.kGold : Colors.white.withValues(alpha: 0.5),
                                        borderRadius: BorderRadius.circular(3),
                                      ),
                                    ),
                                  )),
                                ),
                              ),
                            if (property.images.length > 1 && _imagePageIndex > 0)
                              Positioned(
                                left: 4, top: 0, bottom: 0,
                                child: Center(
                                  child: GestureDetector(
                                    onTap: () => setState(() => _imagePageIndex--),
                                    child: Container(
                                      width: 36, height: 36,
                                      decoration: BoxDecoration(
                                        color: Colors.black26, shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.chevron_left, color: Colors.white, size: 24),
                                    ),
                                  ),
                                ),
                              ),
                            if (property.images.length > 1 && _imagePageIndex < property.images.length - 1)
                              Positioned(
                                right: 4, top: 0, bottom: 0,
                                child: Center(
                                  child: GestureDetector(
                                    onTap: () => setState(() => _imagePageIndex++),
                                    child: Container(
                                      width: 36, height: 36,
                                      decoration: BoxDecoration(
                                        color: Colors.black26, shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.chevron_right, color: Colors.white, size: 24),
                                    ),
                                  ),
                                ),
                              ),
                            Positioned(
                              top: topPadding + 8, left: 16,
                              child: GestureDetector(
                                onTap: () => context.pop(),
                                child: GlassCard(
                                  borderRadius: 24,
                                  blurStrength: 8,
                                  padding: EdgeInsets.zero,
                                  child: Container(
                                    width: 40, height: 40,
                                    alignment: Alignment.center,
                                    child: Icon(Icons.arrow_back_ios, color: isDark ? Colors.white : Colors.black87, size: 20),
                                  ),
                                ),
                              ),
                            ),
                            Positioned(
                              top: topPadding + 8, right: 60,
                              child: GlassCard(
                                borderRadius: 24,
                                blurStrength: 8,
                                padding: EdgeInsets.zero,
                                child: GestureDetector(
                                  onTap: () async {
                                    if (!auth.isLoggedIn) {
                                      _requireLogin(action: AppLocalizations.of(context).favorite);
                                    } else {
                                      final messenger = ScaffoldMessenger.of(context);
                                      final msg = AppLocalizations.of(context).errorOccurred;
                                      final ok = await fav.toggleFavorite(property.id);
                                      if (!ok && mounted) {
                                        messenger.showSnackBar(SnackBar(
                                          content: Text(msg),
                                          duration: const Duration(seconds: 2),
                                        ));
                                      }
                                    }
                                  },
                                  child: Container(
                                    width: 40, height: 40,
                                    alignment: Alignment.center,
                                    child: Icon(
                                      auth.isLoggedIn && fav.isFavorite(property.id)
                                          ? Icons.favorite : Icons.favorite_border,
                                      color: auth.isLoggedIn && fav.isFavorite(property.id)
                                          ? MaskanColors.kDanger : isDark ? Colors.white : Colors.black87,
                                      size: 20,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            Positioned(
                              bottom: 20, left: 16,
                              child: GlassCard(
                                variant: GlassVariant.strong,
                                borderRadius: 20,
                                blurStrength: 8,
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                child: Text(
                                  _typeLabel(context, property.propertyType),
                                  style: const TextStyle(
                                    color: Colors.white, fontWeight: FontWeight.w600,
                                    fontSize: 13, fontFamily: 'Cairo',
                                  ),
                                ),
                              ),
                            ),
                            Positioned(
                              bottom: 20, right: 16,
                              child: GoldBadge(
                                label: '${property.priceFormatted} ${AppLocalizations.of(context).perMonth}',
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                        child: GlassCard(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(property.title, style: TextStyle(
                                fontSize: 22, fontWeight: FontWeight.w600,
                                color: textPrimary, fontFamily: 'Cairo',
                              )),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Icon(Icons.location_on, size: 16, color: MaskanColors.kBlue),
                                  const SizedBox(width: 4),
                                  Expanded(
                                    child: Text(property.location,
                                      style: TextStyle(
                                        fontSize: 14, color: textSecondary, fontFamily: 'Cairo',
                                      )),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              if (property.avgRating > 0)
                                Row(
                                  children: [
                                    RatingBarIndicator(
                                      rating: property.avgRating,
                                      itemSize: 18,
                                      itemBuilder: (_, _) => const Icon(Icons.star, color: MaskanColors.kGold),
                                    ),
                                    const SizedBox(width: 4),
                                    Text('${property.avgRating.toStringAsFixed(1)} (${property.reviewCount} ${AppLocalizations.of(context).reviews})',
                                      style: TextStyle(
                                        fontSize: 13, color: textSecondary, fontFamily: 'Cairo',
                                      )),
                                  ],
                                ),
                              const SizedBox(height: 16),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: [
                                  _specChip(Icons.bed_outlined, '${property.roomsCount} ${AppLocalizations.of(context).rooms}'),
                                  _specChip(Icons.bathtub_outlined, '${property.bathroomsCount} ${AppLocalizations.of(context).bathrooms}'),
                                ],
                              ),
                              const SizedBox(height: 24),
                              _SectionHeader(title: AppLocalizations.of(context).description),
                              const SizedBox(height: 8),
                              Text(
                                property.description,
                                style: TextStyle(
                                  fontSize: 14, color: textSecondary,
                                  fontFamily: 'Cairo', height: 1.6,
                                ),
                                maxLines: _showFullDescription ? null : 4,
                                overflow: _showFullDescription ? null : TextOverflow.ellipsis,
                              ),
                              if (property.description.length > 200)
                                GestureDetector(
                                  onTap: () => setState(() => _showFullDescription = !_showFullDescription),
                                  child: Padding(
                                    padding: const EdgeInsets.only(top: 4),
                                    child: Text(
                                      _showFullDescription ? AppLocalizations.of(context).showLess : AppLocalizations.of(context).showMore,
                                      style: TextStyle(
                                        color: MaskanColors.kBlue, fontSize: 13, fontFamily: 'Cairo',
                                      ),
                                    ),
                                  ),
                                ),
                              const SizedBox(height: 24),
                              _SectionHeader(title: AppLocalizations.of(context).amenities),
                              const SizedBox(height: 8),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: [
                                  _amenityChip(Icons.wifi, 'واي فاي'),
                                  _amenityChip(Icons.ac_unit, 'تكييف'),
                                  _amenityChip(Icons.local_parking, 'موقف سيارات'),
                                  _amenityChip(Icons.security, 'أمن'),
                                  _amenityChip(Icons.local_laundry_service, 'غسيل ملابس'),
                                ],
                              ),
                              const SizedBox(height: 24),
                              if (property.ownerName != null) ...[
                                _SectionHeader(title: AppLocalizations.of(context).ownerLabel),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 24,
                                      backgroundColor: MaskanColors.kBlue.withValues(alpha: 0.3),
                                      child: Text(property.ownerName![0], style: TextStyle(
                                        color: textPrimary, fontFamily: 'Cairo',
                                      )),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(property.ownerName!, style: TextStyle(
                                            fontSize: 15, fontWeight: FontWeight.w600,
                                            color: textPrimary, fontFamily: 'Cairo',
                                          )),
                                          Text(AppLocalizations.of(context).realEstateAgent, style: TextStyle(
                                            fontSize: 13, color: textSecondary, fontFamily: 'Cairo',
                                          )),
                                        ],
                                      ),
                                    ),
                                    OutlinedButton(
                                      onPressed: () {
                                        if (!auth.isLoggedIn) {
                                          _requireLogin(action: AppLocalizations.of(context).message);
                                        } else {
                                          context.push(AppRoutes.chat.replaceFirst(':conversationId', '${property.ownerId}'));
                                        }
                                      },
                                      style: OutlinedButton.styleFrom(
                                        side: BorderSide(color: MaskanColors.kBlue),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      ),
                                      child: Text(AppLocalizations.of(context).message, style: TextStyle(
                                        color: MaskanColors.kBlue, fontFamily: 'Cairo',
                                      )),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 24),
                              ],
                              _SectionHeader(title: AppLocalizations.of(context).reviews),
                              const SizedBox(height: 12),
                              if (reviewProvider.isLoading)
                                const Center(child: CircularProgressIndicator())
                              else if (reviewProvider.reviews.isEmpty)
                                Text(AppLocalizations.of(context).noReviews, style: TextStyle(
                                  color: textSecondary, fontFamily: 'Cairo',
                                ))
                              else
                                ...reviewProvider.reviews.take(3).map((r) => _reviewCard(r)),
                              if (reviewProvider.reviews.length > 3)
                                TextButton(
                                  onPressed: () {},
                                  child: Text(AppLocalizations.of(context).viewAllReviews, style: TextStyle(
                                    color: MaskanColors.kBlue, fontFamily: 'Cairo',
                                  )),
                                ),
                              const SizedBox(height: 24),
                              _SectionHeader(title: AppLocalizations.of(context).locationMap),
                              const SizedBox(height: 12),
                              GlassCard(
                                borderRadius: 16,
                                padding: EdgeInsets.zero,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    ClipRRect(
                                      borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                                      child: SizedBox(
                                        height: 240,
                                        child: FlutterMap(
                                          options: MapOptions(
                                            initialCenter: LatLng(
                                              property.latitude ?? 32.8872,
                                              property.longitude ?? 13.1913,
                                            ),
                                            initialZoom: 15,
                                            interactionOptions: const InteractionOptions(
                                              flags: InteractiveFlag.all,
                                            ),
                                          ),
                                          children: [
                                            TileLayer(
                                              urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                              userAgentPackageName: 'com.maskan.app',
                                            ),
                                            if (property.latitude != null && property.longitude != null)
                                              MarkerLayer(
                                                markers: [
                                                  Marker(
                                                    point: LatLng(property.latitude!, property.longitude!),
                                                    width: 40,
                                                    height: 40,
                                                    child: Icon(
                                                      Icons.location_on,
                                                      color: MaskanColors.kDanger,
                                                      size: 36,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                          ],
                                        ),
                                      ),
                                    ),
                                    InkWell(
                                      onTap: () => launchUrl(Uri.parse(
                                        'https://www.google.com/maps/search/?api=1&query=${property.latitude ?? 32.8872},${property.longitude ?? 13.1913}'),
                                        mode: LaunchMode.externalApplication,
                                      ),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                        child: Row(
                                          children: [
                                            Icon(Icons.open_in_new, size: 16, color: MaskanColors.kBlue),
                                            const SizedBox(width: 8),
                                            Text(property.location, style: TextStyle(
                                              color: MaskanColors.kBlue, fontFamily: 'Cairo', fontSize: 13,
                                            )),
                                            const Spacer(),
                                            Icon(Icons.chevron_left, size: 18, color: textMuted),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 80),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
      bottomNavigationBar: property != null && !provider.isLoading
          ? GlassCard(
              borderRadius: 0,
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
              variant: GlassVariant.strong,
              child: SafeArea(
                child: Row(
                  children: [
                    GestureDetector(
                      onTap: () {
                        final loc = AppLocalizations.of(context);
                        final shareText = '${property.title}\n'
                            '${property.priceFormatted} / ${loc.perMonth}\n'
                            '${property.location}\n\n'
                            '- Maskan';
                        Share.share(shareText);
                      },
                      child: Container(
                        width: 48, height: 48,
                        decoration: BoxDecoration(
                          color: chipBg,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(Icons.share, color: textSecondary, size: 22),
                      ),
                    ),
                    const SizedBox(width: 12),
                    GestureDetector(
                      onTap: () {
                        if (!auth.isLoggedIn) {
                          _requireLogin(action: AppLocalizations.of(context).favorite);
                        } else {
                          fav.toggleFavorite(property.id);
                        }
                      },
                      child: Container(
                        width: 48, height: 48,
                        decoration: BoxDecoration(
                          color: chipBg,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(
                          auth.isLoggedIn && fav.isFavorite(property.id)
                              ? Icons.favorite : Icons.favorite_border,
                          color: auth.isLoggedIn && fav.isFavorite(property.id)
                              ? MaskanColors.kDanger : textSecondary,
                          size: 22,
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: PrimaryButton(
                        label: auth.isLoggedIn ? AppLocalizations.of(context).bookNow : AppLocalizations.of(context).signupToBook,
                        backgroundColor: MaskanColors.kGold,
                        onPressed: () {
                          if (!auth.isLoggedIn) {
                            _requireLogin(action: AppLocalizations.of(context).bookNow);
                          } else {
                            context.push(AppRoutes.bookingForm.replaceFirst(':propertyId', '${property.id}'));
                          }
                        },
                      ),
                    ),
                  ],
                ),
              ),
            )
          : null,
    );
  }

  /// يبني شريحة لعرض مواصفات العقار (مثال: غرف، حمامات).
  Widget _specChip(IconData icon, String label) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final chipBg = isDark ? const Color(0x1AFFFFFF) : const Color(0x1A2D5F8A);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: chipBg,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: context.textSecondary),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(
            fontSize: 13, color: context.textSecondary, fontFamily: 'Cairo',
          )),
        ],
      ),
    );
  }

  /// يبني شريحة لعرض وسيلة راحة مع أيقونة وتسمية.
  Widget _amenityChip(IconData icon, String label) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final chipBg = isDark ? const Color(0x1AFFFFFF) : const Color(0x1A2D5F8A);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: chipBg,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: MaskanColors.kBlue.withValues(alpha: 0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: MaskanColors.kBlue),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(
            fontSize: 13, color: MaskanColors.kBlue, fontFamily: 'Cairo',
          )),
        ],
      ),
    );
  }

  /// يبني بطاقة تعرض تقييماً واحداً مع معلومات المقيّم والتقييم والتعليق.
  Widget _reviewCard(Review review) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0x1AFFFFFF) : const Color(0x1A2D5F8A);
    return Container(
      padding: const EdgeInsets.all(12),
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: MaskanColors.kBlue.withValues(alpha: 0.3),
                child: Text(review.reviewerName?[0] ?? '?', style: TextStyle(
                  color: context.textPrimary, fontSize: 12, fontFamily: 'Cairo',
                )),
              ),
              const SizedBox(width: 8),
              Expanded(child: Text(review.reviewerName ?? 'مستخدم', style: TextStyle(
                fontWeight: FontWeight.w600, color: context.textPrimary, fontFamily: 'Cairo',
              ))),
              RatingBarIndicator(
                rating: review.stars.toDouble(),
                itemSize: 16,
                itemBuilder: (_, _) => const Icon(Icons.star, color: MaskanColors.kGold),
              ),
            ],
          ),
          if (review.comment != null) ...[
            const SizedBox(height: 8),
            Text(review.comment!, style: TextStyle(
              color: context.textSecondary, fontFamily: 'Cairo',
            )),
          ],
        ],
      ),
    );
  }
}

/// رأس قسم بشريط أزرق وعنوان
class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 3, height: 18,
          decoration: BoxDecoration(
            color: MaskanColors.kBlue,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 8),
        Text(title, style: TextStyle(
          fontSize: 17, fontWeight: FontWeight.w600,
          color: MaskanColors.kBlue, fontFamily: 'Cairo',
        )),
      ],
    );
  }
}
