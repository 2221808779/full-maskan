import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/loading_widget.dart';
import '../../core/widgets/empty_state.dart';
import '../../providers/booking_provider.dart';
import '../../models/booking.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض جميع حجوزات المستأجر الحالي مع مؤشرات الحالة وصور العقارات
class BookingListScreen extends StatefulWidget {
  const BookingListScreen({super.key});

  @override
  State<BookingListScreen> createState() => _BookingListScreenState();
}

/// حالة [BookingListScreen] — تحميل وعرض حجوزات المستخدم
class _BookingListScreenState extends State<BookingListScreen> {
  /// يحمّل الحجوزات عند التهيئة.
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<BookingProvider>().loadBookings();
    });
  }

  /// يبني شاشة قائمة الحجوزات مع حالات التحميل والفارغة والممتلئة.
  @override
  Widget build(BuildContext context) {
    final provider = context.watch<BookingProvider>();
    final loc = AppLocalizations.of(context);
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(loc.myBookings, style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo')),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: provider.isLoading
          ? const LoadingWidget()
          : provider.bookings.isEmpty
              ? EmptyState(icon: Icons.calendar_month, title: AppLocalizations.of(context)!.noBookings,
                  subtitle: AppLocalizations.of(context)!.bookYourFirstProperty,
                  actionLabel: AppLocalizations.of(context)!.browseProperties,
                  onAction: _navigateToProperties,
                )
              : RefreshIndicator(
                  onRefresh: () => provider.loadBookings(),
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: provider.bookings.length,
                    itemBuilder: (_, i) => _bookingCard(provider.bookings[i]),
                  ),
                ),
    );
  }

  /// ينتقل إلى شاشة قائمة العقارات.
  void _navigateToProperties() => context.push(AppRoutes.propertyList);

  /// يبني عنصراً نائباً عند عدم توفر صورة الحجز.
  Widget _buildImagePlaceholder() {
    return Container(
      width: double.infinity, height: 200,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [MaskanColors.kBlue.withValues(alpha: 0.5), MaskanColors.kBlue.withValues(alpha: 0.2)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
      ),
      child: Center(child: Icon(Icons.home_rounded, color: Colors.white54, size: 48)),
    );
  }

  /// يبني عنصر بطاقة لحجز واحد مع الصورة والحالة والتواريخ والسعر.
  Widget _bookingCard(Booking booking) {
    final hasImage = booking.propertyImage != null && booking.propertyImage!.isNotEmpty;
    final loc = AppLocalizations.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: GestureDetector(
        onTap: () => context.push(AppRoutes.bookingDetail, extra: booking.id),
        child: GlassCard(
          softMode: true,
          padding: EdgeInsets.zero,
          borderRadius: 16,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                child: SizedBox(
                  width: double.infinity,
                  height: 200,
                  child: Stack(
                    children: [
                      if (hasImage)
                        CachedNetworkImage(
                          imageUrl: AppConstants.resolveImageUrl(booking.propertyImage!),
                          width: double.infinity,
                          height: 200,
                          fit: BoxFit.cover,
                          placeholder: (_, _) => _buildImagePlaceholder(),
                          errorWidget: (_, _, _) => _buildImagePlaceholder(),
                        )
                      else
                        _buildImagePlaceholder(),
                      Positioned(
                        top: 12, right: 12,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(
                            color: _statusColor(booking.status).withValues(alpha: 0.9),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2)),
                            ],
                          ),
                          child: Text(booking.statusLabel,
                            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold, fontFamily: 'Cairo'),
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 0, left: 0, right: 0,
                        child: Container(
                          height: 100,
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.topCenter,
                              end: Alignment.bottomCenter,
                              colors: [Colors.transparent, Colors.black.withValues(alpha: 0.7)],
                            ),
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 14, left: 14, right: 14,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(booking.propertyTitle ?? 'عقار #${booking.propertyId}',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17, fontFamily: 'Cairo'),
                              maxLines: 1, overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(Icons.calendar_today_rounded, color: Colors.white70, size: 13),
                                const SizedBox(width: 4),
                                Text(_formatDate(booking.startDate),
                                  style: const TextStyle(color: Colors.white70, fontSize: 12, fontFamily: 'Cairo'),
                                ),
                                const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 4),
                                  child: Text('—', style: TextStyle(color: Colors.white70, fontSize: 12)),
                                ),
                                Text(_formatDate(booking.endDate),
                                  style: const TextStyle(color: Colors.white70, fontSize: 12, fontFamily: 'Cairo'),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                child: Row(
                  children: [
                    Text('${booking.totalPrice.toStringAsFixed(0)} ${loc.currencyLyd}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: MaskanColors.kGold, fontFamily: 'Cairo'),
                    ),
                    const Spacer(),
                    Text(loc.bookingDetails, style: TextStyle(color: context.textSecondary, fontSize: 13, fontFamily: 'Cairo')),
                    const SizedBox(width: 4),
                    Icon(Icons.chevron_left_rounded, color: context.textSecondary, size: 20),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// يُنسّق [DateTime] كنص بالصيغة `yyyy-MM-dd`.
  String _formatDate(DateTime dt) {
    return '${dt.year.toString()}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}';
  }

  /// يُرجع لوناً يتوافق مع حالة الحجز.
  Color _statusColor(String status) {
    switch (status) {
      case 'confirmed': return MaskanColors.kSuccess;
      case 'active': return MaskanColors.kBlue;
      case 'cancelled': return MaskanColors.danger;
      case 'completed': return MaskanColors.gray400;
      default: return MaskanColors.kWarning;
    }
  }
}
