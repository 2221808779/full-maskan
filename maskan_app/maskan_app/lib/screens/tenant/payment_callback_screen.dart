import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/api/api_client.dart';
import '../../core/api/api_endpoints.dart';
import '../../l10n/app_localizations.dart';

/// شاشة معالجة العودة من بوابة Plutu
///
/// تُفتح عند استقبال deep link من Plutu بعد الدفع:
/// - [callback] /payment/callback — نجاح الدفع (تأكيد مع Plutu)
/// - [cancel] /payment/cancel — إلغاء الدفع
///
/// تستقبل [bookingId] كـ query parameter وتتحقق من حالة الدفع عبر API.
class PaymentCallbackScreen extends StatefulWidget {
  /// نوع المسار: 'callback' للنجاح أو 'cancel' للإلغاء
  final String type;
  const PaymentCallbackScreen({super.key, required this.type});

  @override
  State<PaymentCallbackScreen> createState() => _PaymentCallbackScreenState();
}

/// منطق حالة [PaymentCallbackScreen] — التحقق من حالة الدفع عبر API Plutu وعرض واجهة النجاح/الفشل
class _PaymentCallbackScreenState extends State<PaymentCallbackScreen> {
  /// Whether the payment status is still being verified.
  bool _isChecking = true;

  /// Whether the payment was confirmed successfully.
  bool _isSuccess = false;

  /// The booking ID extracted from the deep-link query parameters.
  int? _bookingId;

  @override
  void initState() {
    super.initState();
    _handleCallback();
  }

  /// Handles the deep-link callback: cancels immediately for cancel type,
  /// otherwise extracts [bookingId] and verifies payment via Plutu check API.
  Future<void> _handleCallback() async {
    if (widget.type == 'cancel') {
      setState(() { _isChecking = false; _isSuccess = false; });
      return;
    }

    try {
      final uri = Uri.base;
      _bookingId = int.tryParse(uri.queryParameters['booking_id'] ?? '');
      if (_bookingId == null) {
        setState(() { _isChecking = false; _isSuccess = false; });
        return;
      }
      final response = await ApiClient().post(ApiEndpoints.plutuCheck(_bookingId!));
      setState(() {
        _isSuccess = response.statusCode == 200;
        _isChecking = false;
      });
    } catch (_) {
      if (mounted) setState(() { _isChecking = false; _isSuccess = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.payment),
        elevation: 0, scrolledUnderElevation: 0, centerTitle: true,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              if (_isChecking) ...[
                const CircularProgressIndicator(color: MaskanColors.kBlue),
                const SizedBox(height: 24),
                Text(AppLocalizations.of(context)!.checkingPaymentStatus, style: TextStyle(fontSize: 16, fontFamily: 'Cairo')),
              ] else ...[
                Icon(
                  _isSuccess ? Icons.check_circle_outline : Icons.cancel_outlined,
                  size: 80,
                  color: _isSuccess ? MaskanColors.kSuccess : MaskanColors.kDanger,
                ),
                const SizedBox(height: 24),
                Text(
                  _isSuccess ? AppLocalizations.of(context)!.paymentSuccess : AppLocalizations.of(context)!.paymentFailed,
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, fontFamily: 'Cairo'),
                ),
                const SizedBox(height: 8),
                Text(
                  _isSuccess
                      ? AppLocalizations.of(context)!.thankYouBookingConfirmed
                      : widget.type == 'cancel'
                          ? AppLocalizations.of(context)!.paymentCancelled
                          : AppLocalizations.of(context)!.paymentErrorContactSupport,
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 14, color: context.textSecondary, fontFamily: 'Cairo'),
                ),
                const SizedBox(height: 32),
                PrimaryButton(
                  label: _isSuccess ? AppLocalizations.of(context)!.backToHome : AppLocalizations.of(context)!.backToBookings,
                  onPressed: () => context.go(_isSuccess ? '/tenant-home' : '/bookings'),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
