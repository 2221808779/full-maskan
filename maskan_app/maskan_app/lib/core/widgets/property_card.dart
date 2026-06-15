import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/constants.dart';
import '../../config/colors.dart';
import '../../models/property.dart';
import '../design/design_tokens.dart';

/// بطاقة عقار تعرض الصورة والعنوان والموقع والسعر والنوع والتقييم مع إمكانية إضافة المفضلة
/// تدعم وضعين للعرض: كامل (مع تفاصيل الغرف والحمامات) ومضغوط
class PropertyCard extends StatelessWidget {
  /// The property data to display.
  final Property property;

  /// Called when the card is tapped.
  final VoidCallback? onTap;

  /// Whether to show the favorite toggle button.
  final bool showFavorite;

  /// Whether the property is currently favorited.
  final bool isFavorite;

  /// Called when the favorite toggle is tapped.
  final VoidCallback? onFavoriteTap;

  /// If true, renders a compact version of the card.
  final bool compact;

  const PropertyCard({
    super.key,
    required this.property,
    this.onTap,
    this.showFavorite = true,
    this.isFavorite = false,
    this.onFavoriteTap,
    this.compact = false,
  });

  /// ترجمة نوع العقار من الإنجليزية إلى العربية
  String _translateType(String type) {
    switch (type.toLowerCase()) {
      case 'apartment': return 'شقة';
      case 'villa': return 'فيلا';
      case 'studio': return 'استوديو';
      case 'shop': return 'متجر';
      case 'office': return 'مكتب';
      case 'warehouse': return 'مستودع';
      case 'land': return 'أرض';
      default: return type;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final accentColor = isDark ? MaskanColors.kBlueSky : MaskanColors.kBlue;
    final mutedColor = isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(
        color: isDark ? MaskanColors.kBgCard2 : const Color(0xF0FFFFFF),
        borderRadius: BorderRadius.circular(20),
        boxShadow: isDark
            ? DesignTokens.softShadowDark()
            : DesignTokens.softShadowLight(),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            LayoutBuilder(
              builder: (context, constraints) {
                return SizedBox(
                  height: compact ? 120 : constraints.maxWidth * 0.55,
                  child: Stack(
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                        child: property.images.isNotEmpty
                            ? CachedNetworkImage(
                                imageUrl: AppConstants.resolveImageUrl(property.images.first),
                                width: double.infinity,
                                height: double.infinity,
                                fit: BoxFit.cover,
                                placeholder: (_, _) => _buildGradient(),
                                errorWidget: (_, _, _) => _buildGradient(),
                              )
                            : _buildGradient(),
                      ),
                      Positioned(
                        bottom: 0, left: 0, right: 0,
                        height: 80,
                        child: Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: [Colors.black54, Colors.transparent],
                            ),
                          ),
                        ),
                      ),
                      if (showFavorite)
                        Positioned(
                          top: 8, left: 8,
                          child: InkWell(
                            onTap: onFavoriteTap,
                            child: Container(
                              width: 26, height: 26,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.black.withValues(alpha: 0.25),
                              ),
                              child: Icon(
                                isFavorite ? Icons.favorite : Icons.favorite_border,
                                size: 14,
                                color: isFavorite ? MaskanColors.danger : Colors.white,
                              ),
                            ),
                          ),
                        ),
                      Positioned(
                        top: 8, right: 8,
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: BackdropFilter(
                            filter: ImageFilter.blur(sigmaX: 6, sigmaY: 6),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: MaskanColors.kGold.withValues(alpha: 0.3),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '${property.priceFormatted} د.ل',
                                style: const TextStyle(
                                  color: MaskanColors.kGoldLight,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'Cairo',
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                      if (!compact)
                        Positioned(
                          bottom: 8, left: 8,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                            ),
                            child: Text(
                              _translateType(property.propertyType),
                              style: const TextStyle(
                                color: Colors.white, fontSize: 9,
                                fontWeight: FontWeight.w600, fontFamily: 'Cairo',
                              ),
                            ),
                          ),
                        ),
                      if (compact && property.avgRating > 0)
                        Positioned(
                          bottom: 8, left: 8,
                          child: Row(
                            children: [
                              const Icon(Icons.star, size: 10, color: Colors.amber),
                              const SizedBox(width: 2),
                              Text(
                                property.avgRating.toStringAsFixed(1),
                                style: const TextStyle(
                                  color: Colors.white, fontSize: 9,
                                  fontFamily: 'Cairo',
                                ),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                );
              },
            ),
            Padding(
              padding: EdgeInsets.all(compact ? 10 : 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    property.title,
                    style: TextStyle(
                      fontSize: compact ? 10 : 13,
                      fontWeight: FontWeight.bold,
                      color: accentColor,
                      fontFamily: 'Cairo',
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.location_on, size: 10, color: mutedColor),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text(
                          property.location,
                          style: TextStyle(fontSize: 9, color: mutedColor, fontFamily: 'Cairo'),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  if (!compact) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Flexible(child: Text('${property.roomsCount} غرف · ${property.bathroomsCount} حمام · ${((property.roomsCount + property.bathroomsCount) * 15).toString()}م²', overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 10, color: mutedColor, fontFamily: 'Cairo'))),
                        const Spacer(),
                        if (property.avgRating > 0)
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.star, size: 12, color: Colors.amber),
                              const SizedBox(width: 2),
                              Text(
                                property.avgRating.toStringAsFixed(1),
                                style: TextStyle(fontSize: 10, color: mutedColor, fontFamily: 'Cairo'),
                              ),
                            ],
                          ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// بناء عنصر نائب متدرج يُستخدم عند عدم توفر صورة
  Widget _buildGradient() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF2D5F8A), Color(0xFF1B3A52)],
        ),
      ),
    );
  }
}
