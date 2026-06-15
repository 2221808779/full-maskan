import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:dio/dio.dart' show DioException;
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/api/api_client.dart';
import '../../core/api/api_endpoints.dart';
import '../../core/utils/helpers.dart';
import '../../providers/booking_provider.dart';
import '../../models/booking.dart';
import '../../l10n/app_localizations.dart';

/// شاشة الدفع — معالجة الدفع الإلكتروني عبر Plutu أو الدفع النقدي
///
/// الميزات:
/// - اختيار طريقة الدفع (Plutu للبطاقات الإلكترونية / نقدي عند الاستلام)
/// - عرض تفاصيل الحجز (العقار، التواريخ، المبلغ)
/// - عند اختيار Plutu: استدعاء API [plutuInitiate] وتوجيه المستخدم لبوابة الدفع
/// - عند اختيار cash: تأكيد الحجز مباشرة
class PaymentScreen extends StatefulWidget {
  /// معرف الحجز المطلوب دفعه
  final int bookingId;
  const PaymentScreen({super.key, required this.bookingId});

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

/// منطق حالة [PaymentScreen] — إدارة اختيار طريقة الدفع وتحميل تفاصيل الحجز ومعالجة Plutu أو النقدي
class _PaymentScreenState extends State<PaymentScreen> {
  /// طريقة الدفع المختارة: 'plutu' أو 'cash'
  String _selectedMethod = 'plutu';

  /// Whether a payment request is currently being processed.
  bool _isProcessing = false;

  /// The booking associated with this payment.
  Booking? _booking;

  @override
  void initState() {
    super.initState();
    _loadBooking();
  }

  /// Loads the booking details from [BookingProvider] matching [widget.bookingId].
  void _loadBooking() {
    final provider = context.read<BookingProvider>();
    final bookings = provider.bookings;
    if (bookings.isNotEmpty) {
      _booking = bookings.firstWhere(
        (b) => b.id == widget.bookingId,
        orElse: () => bookings.first,
      );
      setState(() {});
    }
  }

  /// Initiates the payment flow: launches Plutu gateway for online payment
  /// or confirms booking with cash-on-delivery.
  Future<void> _processPayment() async {
    setState(() => _isProcessing = true);
    try {
      if (_selectedMethod == 'plutu') {
        final response = await ApiClient().post(ApiEndpoints.plutuInitiate, data: {
          'booking_id': widget.bookingId,
          'amount': _booking?.totalPrice.toStringAsFixed(2) ?? '0',
          'currency': 'LYD',
          'return_url': 'maskanapp://payment/callback',
          'cancel_url': 'maskanapp://payment/cancel',
        });
        final redirectUrl = response.data['redirect_url'] ?? response.data['data']?['redirect_url'];
        if (redirectUrl != null && redirectUrl.isNotEmpty) {
          await launchUrl(Uri.parse(redirectUrl), mode: LaunchMode.externalApplication);
        } else if (mounted) {
          Helpers.showSnackBar(context, AppLocalizations.of(context)!.paymentGatewayFailed, isError: true);
        }
      } else {
        Helpers.showSnackBar(context, AppLocalizations.of(context)!.bookingCashOnDelivery);
        if (mounted) context.pop();
      }
      if (mounted) setState(() => _isProcessing = false);
    } catch (e) {
      if (mounted) setState(() => _isProcessing = false);
      if (mounted) {
        String msg = AppLocalizations.of(context)!.paymentProcessingFailed;
        if (e is DioException && e.response?.data is Map) {
          final data = e.response!.data as Map;
          msg = data['message']?.toString() ?? msg;
          if (data['amount_exceeded'] == true) {
            Helpers.showSnackBar(context, msg, isError: true);
            return;
          }
        }
        Helpers.showSnackBar(context, msg, isError: true);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary;
    final mutedColor = isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary;

    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.payment),
        elevation: 0, scrolledUnderElevation: 0, centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (_booking != null) ...[
              GlassCard(
                borderRadius: 16,
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(AppLocalizations.of(context)!.bookingDetails, style: TextStyle(
                      fontSize: 17, fontWeight: FontWeight.bold, fontFamily: 'Cairo',
                    )),
                    const SizedBox(height: 16),
                    _buildInfoRow(Icons.home_outlined, AppLocalizations.of(context)!.property, _booking!.propertyTitle ?? '---', textColor: mutedColor),
                    const SizedBox(height: 10),
                    _buildInfoRow(Icons.calendar_today, AppLocalizations.of(context)!.startDate, _booking!.startDate.toString().substring(0, 10), textColor: mutedColor),
                    const SizedBox(height: 10),
                    _buildInfoRow(Icons.calendar_today, AppLocalizations.of(context)!.endDate, _booking!.endDate.toString().substring(0, 10), textColor: mutedColor),
                    const SizedBox(height: 10),
                    _buildInfoRow(Icons.nights_stay, AppLocalizations.of(context)!.numberOfNights, '${_booking!.nights}', textColor: mutedColor),
                    const Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(AppLocalizations.of(context)!.totalAmount, style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold, fontFamily: 'Cairo',
                        )),
                        Text('${_booking!.totalPrice.toStringAsFixed(0)} ${AppLocalizations.of(context)!.currencyLyd}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 22,
                            color: MaskanColors.kGold, fontFamily: 'Cairo',
                          )),
                      ],
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 24),
            Text(AppLocalizations.of(context)!.paymentMethod, style: TextStyle(
              fontSize: 17, fontWeight: FontWeight.bold, fontFamily: 'Cairo',
            )),
            const SizedBox(height: 12),
            _buildPaymentMethod(
              icon: Icons.credit_card_rounded,
              title: AppLocalizations.of(context)!.plutuOnlinePayment,
              subtitle: AppLocalizations.of(context)!.creditCardOrEWallet,
              value: 'plutu',
              isDark: isDark,
              textColor: textColor,
              mutedColor: mutedColor,
            ),
            const SizedBox(height: 8),
            _buildPaymentMethod(
              icon: Icons.money_rounded,
              title: AppLocalizations.of(context)!.cashOnDelivery,
              subtitle: AppLocalizations.of(context)!.cashOnHandover,
              value: 'cash',
              isDark: isDark,
              textColor: textColor,
              mutedColor: mutedColor,
            ),
            const SizedBox(height: 20),
            if (_selectedMethod == 'plutu')
              GlassCard(
                variant: GlassVariant.blue,
                borderRadius: 14,
                padding: const EdgeInsets.all(14),
                child: Row(
                  children: [
                    const Icon(Icons.shield_outlined, color: MaskanColors.kBlue, size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(AppLocalizations.of(context)!.plutuRedirectInfo,
                        style: TextStyle(fontSize: 13, color: mutedColor, fontFamily: 'Cairo')),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 24),
            PrimaryButton(
              label: _selectedMethod == 'plutu' ? AppLocalizations.of(context)!.proceedToPlutu : AppLocalizations.of(context)!.confirmBooking,
              isLoading: _isProcessing,
              backgroundColor: _selectedMethod == 'plutu'
                  ? MaskanColors.kBlue
                  : MaskanColors.kGold,
              onPressed: _processPayment,
            ),
          ],
        ),
      ),
    );
  }

  /// Builds a single info row with an icon, label, and value for booking details.
  Widget _buildInfoRow(IconData icon, String label, String value, {Color textColor = MaskanColors.kTextSecondary}) {
    return Row(
      children: [
        Icon(icon, size: 18, color: MaskanColors.kBlue),
        const SizedBox(width: 8),
        Text('$label: ', style: TextStyle(color: textColor, fontFamily: 'Cairo')),
        Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontFamily: 'Cairo')),
      ],
    );
  }

  /// Builds a selectable payment-method card (Plutu or Cash) with icon, title,
  /// subtitle, and a radio-style indicator.
  Widget _buildPaymentMethod({
    required IconData icon,
    required String title,
    required String subtitle,
    required String value,
    required bool isDark,
    required Color textColor,
    required Color mutedColor,
  }) {
    final selected = _selectedMethod == value;
    return GestureDetector(
      onTap: () => setState(() => _selectedMethod = value),
      child: GlassCard(
        variant: selected ? GlassVariant.blue : GlassVariant.normal,
        borderRadius: 14,
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 48, height: 48,
              decoration: BoxDecoration(
                color: selected
                    ? MaskanColors.kBlue.withValues(alpha: 0.2)
                    : (isDark ? const Color(0x1AFFFFFF) : const Color(0x1A4A8DBF)),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: selected ? MaskanColors.kBlue : mutedColor, size: 24),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                    color: selected ? textColor : mutedColor,
                    fontFamily: 'Cairo',
                  )),
                  const SizedBox(height: 2),
                  Text(subtitle, style: TextStyle(
                    fontSize: 12, color: mutedColor, fontFamily: 'Cairo',
                  )),
                ],
              ),
            ),
            Container(
              width: 22, height: 22,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: selected ? MaskanColors.kBlue : Colors.transparent,
                border: Border.all(
                  color: selected ? MaskanColors.kBlue : mutedColor.withValues(alpha: 0.5),
                  width: 2,
                ),
              ),
              child: selected
                  ? const Icon(Icons.check, color: Colors.white, size: 14)
                  : null,
            ),
          ],
        ),
      ),
    );
  }
}