/// شاشة تفاصيل الحجز — تعرض معلومات العقار وتفاصيل التواريخ والدفع والجدول الزمني للحالة وأزرار الإجراءات
library;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/loading_widget.dart';
import '../../providers/booking_provider.dart';
import '../../providers/maintenance_provider.dart';
import '../../providers/review_provider.dart';
import '../../providers/auth_provider.dart';
import '../../models/booking.dart';
import '../../models/maintenance_request.dart';
import '../../models/review.dart';
import '../../l10n/app_localizations.dart';

class BookingDetailScreen extends StatefulWidget {
  /// معرّف الحجز المراد عرضه
  final int bookingId;
  const BookingDetailScreen({super.key, required this.bookingId});

  @override
  State<BookingDetailScreen> createState() => _BookingDetailScreenState();
}

/// حالة [BookingDetailScreen] — تحميل تفاصيل الحجز وطلبات الصيانة
/// والمراجعات.
class _BookingDetailScreenState extends State<BookingDetailScreen> {
  Booking? _booking;
  bool _loading = true;

  /// يُشغّل تحميل البيانات عند التهيئة.
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _load();
    });
  }

  /// يحمّل تفاصيل الحجز وطلبات الصيانة وتقييمات العقار.
  Future<void> _load() async {
    final bp = context.read<BookingProvider>();
    final mp = context.read<MaintenanceProvider>();
    if (bp.bookings.isEmpty) await bp.loadBookings();
    if (mp.requests.isEmpty) await mp.loadRequests();
    if (!mounted) return;
    final booking = bp.bookings.cast<Booking?>().firstWhere(
      (b) => b!.id == widget.bookingId,
      orElse: () => null,
    );
    if (booking != null) {
      final rp = context.read<ReviewProvider>();
      rp.loadPropertyReviews(booking.propertyId);
    }
    if (!mounted) return;
    setState(() {
      _booking = booking;
      _loading = false;
    });
  }

  /// يبني شاشة تفاصيل الحجز مع عرض التحميل أو الخطأ أو المحتوى الكامل.
  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return MaskanScaffold(
        appBar: AppBar(title: Text(AppLocalizations.of(context)!.bookingDetails, style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo')), elevation: 0, scrolledUnderElevation: 0, centerTitle: true),
        body: const LoadingWidget(),
      );
    }
    if (_booking == null) {
      return MaskanScaffold(
        appBar: AppBar(title: Text(AppLocalizations.of(context)!.bookingDetails, style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo')), elevation: 0, scrolledUnderElevation: 0, centerTitle: true),
        body: Center(child: Text(AppLocalizations.of(context)!.propertyNotFound, style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo'))),
      );
    }
    return _buildScreen();
  }

  /// يبني شاشة المحتوى الرئيسي بعد تحميل بيانات الحجز.
  Widget _buildScreen() {
    final booking = _booking!;
    final loc = AppLocalizations.of(context);
    final mp = context.watch<MaintenanceProvider>();
    final rp = context.watch<ReviewProvider>();
    final auth = context.watch<AuthProvider>();
    final propertyRequests = mp.requests.where((r) => r.propertyId == booking.propertyId).toList();
    final existingReview = rp.reviews.cast<Review?>().firstWhere(
      (r) => r!.userId == auth.user?.id,
      orElse: () => null,
    );

    return MaskanScaffold(
      appBar: AppBar(
        title: Text(booking.propertyTitle ?? loc.bookingDetails, style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo'), maxLines: 1, overflow: TextOverflow.ellipsis),
        elevation: 0, scrolledUnderElevation: 0, centerTitle: true,
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          final bp = context.read<BookingProvider>();
          final mp = context.read<MaintenanceProvider>();
          await bp.loadBookings();
          await mp.loadRequests();
          if (mounted) {
            setState(() {
              _booking = bp.bookings.cast<Booking?>().firstWhere(
                (b) => b!.id == widget.bookingId,
                orElse: () => null,
              );
            });
          }
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _buildImageHeader(booking),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 16),
                    _buildStatusBanner(booking),
                    const SizedBox(height: 16),
                    _buildDetailsCard(booking),
                    if (booking.status == 'completed') ...[
                      const SizedBox(height: 12),
                      if (existingReview != null && existingReview.comment != null) ...[
                        _buildExistingReviewCard(existingReview),
                      ] else if (existingReview != null) ...[
                        _buildStarsOnlyCard(existingReview),
                      ] else ...[
                        _buildRatePropertyButton(booking),
                      ],
                    ],
                    const SizedBox(height: 16),
                    _buildMaintenanceSection(booking, propertyRequests),
                    if (booking.canCancel) ...[
                      const SizedBox(height: 12),
                      _buildCancelButton(booking.id),
                    ],
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// يبني قسم صورة العنوان للحجز مع تراكب تدرّجي.
  Widget _buildImageHeader(Booking booking) {
    final hasImage = booking.propertyImage != null && booking.propertyImage!.isNotEmpty;
    return SizedBox(
      height: 220,
      child: Stack(
        children: [
          if (hasImage)
            CachedNetworkImage(
              imageUrl: AppConstants.resolveImageUrl(booking.propertyImage!),
              width: double.infinity,
              height: 220,
              fit: BoxFit.cover,
              errorWidget: (_, _, _) => _buildHeaderPlaceholder(),
            )
          else
            _buildHeaderPlaceholder(),
          Positioned(
            bottom: 0, left: 0, right: 0,
            child: Container(
              height: 80,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.transparent, Colors.black.withValues(alpha: 0.6)],
                ),
              ),
            ),
          ),
          if (booking.propertyAddress != null)
            Positioned(
              bottom: 12, left: 16, right: 16,
              child: Row(
                children: [
                  Icon(Icons.location_on_rounded, color: Colors.white, size: 16),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(booking.propertyAddress!,
                      style: const TextStyle(color: Colors.white, fontSize: 13, fontFamily: 'Cairo'),
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  /// يبني عنصراً نائباً بتدرّج عند عدم وجود صورة العنوان.
  Widget _buildHeaderPlaceholder() {
    return Container(
      width: double.infinity, height: 220,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [MaskanColors.kBlue.withValues(alpha: 0.5), MaskanColors.kBlue.withValues(alpha: 0.2)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
      ),
      child: const Center(child: Icon(Icons.home_rounded, color: Colors.white54, size: 64)),
    );
  }

  /// يبني شريط حالة يُظهر الحالة الحالية للحجز مع أيقونة.
  Widget _buildStatusBanner(Booking booking) {
    final color = _statusColor(booking.status);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(_statusIcon(booking.status), color: color, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(AppLocalizations.of(context)!.bookingDetails,
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: context.textPrimary, fontFamily: 'Cairo'),
                ),
                const SizedBox(height: 2),
                Text(booking.statusLabel,
                  style: TextStyle(color: color, fontWeight: FontWeight.w600, fontSize: 14, fontFamily: 'Cairo'),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(booking.statusLabel,
              style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 13, fontFamily: 'Cairo'),
            ),
          ),
        ],
      ),
    );
  }

  /// يبني بطاقة بتفاصيل الحجز: التواريخ، الليالي، المبلغ الإجمالي، طريقة الدفع.
  Widget _buildDetailsCard(Booking booking) {
    final loc = AppLocalizations.of(context);
    return GlassCard(
      softMode: true,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(loc.bookingDetails, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: context.textPrimary, fontFamily: 'Cairo')),
          const Divider(height: 20),
          _detailRow(loc.startDate, _formatDate(booking.startDate)),
          const SizedBox(height: 10),
          _detailRow(loc.endDate, _formatDate(booking.endDate)),
          const SizedBox(height: 10),
          _detailRow(loc.numberOfNights, '${booking.nights} ${loc.nights}'),
          const Divider(height: 20),
          _detailRow(loc.totalAmount, '${booking.totalPrice.toStringAsFixed(0)} ${loc.currencyLyd}',
            valueStyle: TextStyle(fontWeight: FontWeight.bold, color: MaskanColors.kGold, fontSize: 16, fontFamily: 'Cairo'),
          ),
          const SizedBox(height: 8),
          _detailRow(loc.paymentMethod, booking.paymentMethod == 'plutu' ? loc.plutuOnlinePayment : loc.cashOnHandover),
          if (booking.notes != null && booking.notes!.isNotEmpty) ...[
            const Divider(height: 20),
            Text(loc.notes, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: context.textPrimary, fontFamily: 'Cairo')),
            const SizedBox(height: 4),
            Text(booking.notes!, style: TextStyle(color: context.textSecondary, fontSize: 13, fontFamily: 'Cairo')),
          ],
        ],
      ),
    );
  }

  /// يبني قسم طلبات الصيانة مع قائمة وزر "طلب جديد".
  Widget _buildMaintenanceSection(Booking booking, List<MaintenanceRequest> requests) {
    final loc = AppLocalizations.of(context);
    return GlassCard(
      softMode: true,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.build_outlined, color: MaskanColors.kBlue, size: 20),
              const SizedBox(width: 8),
              Text(loc.maintenanceRequests, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: context.textPrimary, fontFamily: 'Cairo')),
              const Spacer(),
              Text('${requests.length}', style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
            ],
          ),
          const Divider(height: 20),
          if (requests.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 12),
              child: Center(
                child: Column(
                  children: [
                    Icon(Icons.build_circle_outlined, color: context.textMuted, size: 36),
                    const SizedBox(height: 8),
                    Text(loc.noMaintenanceRequests, style: TextStyle(color: context.textMuted, fontFamily: 'Cairo')),
                    const SizedBox(height: 4),
                    Text(loc.sendRequestForActiveBooking, style: TextStyle(color: context.textMuted, fontSize: 12, fontFamily: 'Cairo'), textAlign: TextAlign.center),
                  ],
                ),
              ),
            )
          else
            ...requests.map((r) => _buildMaintenanceItem(r)),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => context.push(AppRoutes.maintenanceForm.replaceAll(':propertyId', '${booking.propertyId}')),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: Text(loc.newRequest),
              style: OutlinedButton.styleFrom(
                foregroundColor: MaskanColors.kBlue,
                side: BorderSide(color: MaskanColors.kBlue.withValues(alpha: 0.4)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// يبني بطاقة تعرض تقييماً حالياً مع التقييم والتعليق.
  Widget _buildExistingReviewCard(Review review) {
    final loc = AppLocalizations.of(context);
    return GlassCard(
      softMode: true,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.star_rounded, color: MaskanColors.kGold, size: 18),
              const SizedBox(width: 6),
              Text(loc.myReview, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: context.textPrimary, fontFamily: 'Cairo')),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              RatingBarIndicator(
                rating: review.stars.toDouble(),
                itemSize: 20,
                itemBuilder: (_, _) => const Icon(Icons.star, color: MaskanColors.kGold),
              ),
              const SizedBox(width: 6),
              Text('${review.stars}/5', style: TextStyle(fontSize: 13, color: context.textSecondary, fontFamily: 'Cairo')),
            ],
          ),
          if (review.comment != null && review.comment!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(review.comment!, style: TextStyle(fontSize: 13, color: context.textPrimary, fontFamily: 'Cairo')),
          ],
        ],
      ),
    );
  }

  /// يبني بطاقة مضغوطة تعرض فقط تقييم النجوم بدون تعليق.
  Widget _buildStarsOnlyCard(Review review) {
    return GlassCard(
      softMode: true,
      child: Row(
        children: [
          const Icon(Icons.star_rounded, color: MaskanColors.kGold, size: 18),
          const SizedBox(width: 6),
          RatingBarIndicator(
            rating: review.stars.toDouble(),
            itemSize: 20,
            itemBuilder: (_, _) => const Icon(Icons.star, color: MaskanColors.kGold),
          ),
          const SizedBox(width: 6),
          Text('${review.stars}/5', style: TextStyle(fontSize: 14, color: context.textPrimary, fontFamily: 'Cairo')),
        ],
      ),
    );
  }

  /// يبني زراً للانتقال إلى نموذج التقييم لتقييم العقار.
  Widget _buildRatePropertyButton(Booking booking) {
    final loc = AppLocalizations.of(context);
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: () => context.push(
          AppRoutes.reviewForm
            .replaceAll(':targetType', 'property')
            .replaceAll(':targetId', '${booking.propertyId}'),
          extra: {'propertyId': booking.propertyId, 'bookingId': booking.id},
        ),
        icon: const Icon(Icons.star_rounded, size: 18),
        label: Text('${loc.rate}${loc.propertyTarget}'),
        style: OutlinedButton.styleFrom(
          foregroundColor: MaskanColors.kGold,
          side: BorderSide(color: MaskanColors.kGold.withValues(alpha: 0.4)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          padding: const EdgeInsets.symmetric(vertical: 12),
        ),
      ),
    );
  }

  /// يبني بطاقة لطلب صيانة واحد مع الحالة وخيار التقييم.
  Widget _buildMaintenanceItem(MaintenanceRequest r) {
    final color = _maintenanceColor(r.status);
    final canRate = r.status == 'completed' && r.technicianId != null;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: context.textPrimary.withValues(alpha: 0.03),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(Icons.build_rounded, color: color, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (r.aiCategory != null)
                        Text(r.aiCategory!,
                          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: context.textPrimary, fontFamily: 'Cairo'),
                        ),
                      Text(r.problemDescription,
                        style: TextStyle(fontSize: 12, color: context.textSecondary, fontFamily: 'Cairo'),
                        maxLines: 2, overflow: TextOverflow.ellipsis,
                      ),
                      if (r.technicianName != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text('${AppLocalizations.of(context)!.technicianLabel}${r.technicianName}',
                            style: TextStyle(fontSize: 11, color: context.textMuted, fontFamily: 'Cairo'),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(r.statusLabel,
                    style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Cairo'),
                  ),
                ),
              ],
            ),
            if (r.technicianId != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => context.push(AppRoutes.chat.replaceFirst(':conversationId', '${r.technicianId}')),
                        icon: const Icon(Icons.chat_bubble_outline, size: 16),
                        label: Text(AppLocalizations.of(context)!.message,
                          style: const TextStyle(fontSize: 12, fontFamily: 'Cairo'),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: MaskanColors.kBlue,
                          side: BorderSide(color: MaskanColors.kBlue.withValues(alpha: 0.3)),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          visualDensity: VisualDensity.compact,
                        ),
                      ),
                    ),
                    if (canRate) ...[
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => context.push(
                            AppRoutes.reviewForm
                              .replaceAll(':targetType', 'technician')
                              .replaceAll(':targetId', '${r.technicianId}'),
                            extra: {'propertyId': _booking?.propertyId},
                          ),
                          icon: const Icon(Icons.star_rounded, size: 16),
                          label: Text('${AppLocalizations.of(context)!.rate}${AppLocalizations.of(context)!.technicianTarget}',
                            style: const TextStyle(fontSize: 12, fontFamily: 'Cairo'),
                          ),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: MaskanColors.kGold,
                            side: BorderSide(color: MaskanColors.kGold.withValues(alpha: 0.3)),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            visualDensity: VisualDensity.compact,
                          ),
                        ),
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

  /// يبني زر إلغاء الحجز مع حوار تأكيد.
  Widget _buildCancelButton(int bookingId) {
    final loc = AppLocalizations.of(context);
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: () => _confirmCancel(bookingId),
        icon: const Icon(Icons.close_rounded, size: 18),
        label: Text(loc.cancel),
        style: OutlinedButton.styleFrom(
          foregroundColor: MaskanColors.danger,
          side: BorderSide(color: MaskanColors.danger.withValues(alpha: 0.4)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          padding: const EdgeInsets.symmetric(vertical: 14),
        ),
      ),
    );
  }

  /// يبني صفاً مع تسمية وقيمة لعرض تفاصيل الحجز.
  Widget _detailRow(String label, String value, {TextStyle? valueStyle}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 100,
          child: Text(label, style: TextStyle(color: context.textSecondary, fontSize: 13, fontFamily: 'Cairo')),
        ),
        Expanded(
          child: Text(value, style: valueStyle ?? TextStyle(color: context.textPrimary, fontSize: 13, fontFamily: 'Cairo')),
        ),
      ],
    );
  }

  /// يُنسّق [DateTime] كنص بالصيغة `yyyy-MM-dd`.
  String _formatDate(DateTime dt) {
    final y = dt.year.toString();
    final m = dt.month.toString().padLeft(2, '0');
    final d = dt.day.toString().padLeft(2, '0');
    return '$y-$m-$d';
  }

  /// يُرجع لوناً يتوافق مع حالة الحجز المُعطاة.
  Color _statusColor(String status) {
    switch (status) {
      case 'confirmed': return MaskanColors.kSuccess;
      case 'active': return MaskanColors.kBlue;
      case 'cancelled': return MaskanColors.danger;
      case 'completed': return MaskanColors.gray400;
      default: return MaskanColors.kWarning;
    }
  }

  /// يُرجع أيقونة تتوافق مع حالة الحجز المُعطاة.
  IconData _statusIcon(String status) {
    switch (status) {
      case 'confirmed': return Icons.check_circle_rounded;
      case 'active': return Icons.play_circle_rounded;
      case 'cancelled': return Icons.cancel_rounded;
      case 'completed': return Icons.task_alt_rounded;
      default: return Icons.schedule_rounded;
    }
  }

  /// يُرجع لوناً يتوافق مع حالة طلب الصيانة المُعطى.
  Color _maintenanceColor(String status) {
    switch (status) {
      case 'completed': return MaskanColors.kSuccess;
      case 'assigned': return MaskanColors.kBlue;
      case 'in_progress': return MaskanColors.kBlue;
      case 'pending': return MaskanColors.kWarning;
      default: return MaskanColors.gray400;
    }
  }

  /// يُظهر حوار تأكيد ويلغي الحجز إذا تم التأكيد.
  void _confirmCancel(int bookingId) {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: isDark ? MaskanColors.kBgCard : MaskanColors.lBgCard,
        title: Text(loc.cancel, style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo')),
        content: Text(loc.confirmCancelBooking, style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(loc.back, style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              context.read<BookingProvider>().cancelBooking(bookingId).then((_) {
                if (mounted) context.read<BookingProvider>().loadBookings();
              });
            },
            style: TextButton.styleFrom(foregroundColor: MaskanColors.danger),
            child: Text(loc.confirmDelete, style: const TextStyle(fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
  }
}
