import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../l10n/app_localizations.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/widgets/maskan_scaffold.dart';

/// شاشة إدخال رمز التحقق OTP المرسل إلى هاتف المستخدم
class OtpVerificationScreen extends StatefulWidget {
  /// The phone number to which the OTP was sent.
  final String phone;
  const OtpVerificationScreen({super.key, required this.phone});

  @override
  State<OtpVerificationScreen> createState() => _OtpVerificationScreenState();
}

/// حالة [OtpVerificationScreen] — إدارة حقول إدخال OTP ومؤقت إعادة الإرسال ورسوم الاهتزاز عند الخطأ
class _OtpVerificationScreenState extends State<OtpVerificationScreen>
    with SingleTickerProviderStateMixin {
  final List<TextEditingController> _controllers = List.generate(
    6,
    (_) => TextEditingController(),
  );
  final List<FocusNode> _focusNodes = List.generate(6, (_) => FocusNode());
  late AnimationController _shakeController;
  late Animation<double> _shakeAnimation;
  int _timerSeconds = 59;
  bool _canResend = false;
  bool _isLoading = false;

  /// Initializes the shake animation controller and starts the resend timer.
  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _shakeAnimation = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: 0, end: 8), weight: 1),
      TweenSequenceItem(tween: Tween(begin: -8, end: 0), weight: 1),
      TweenSequenceItem(tween: Tween(begin: 0, end: -6), weight: 1),
      TweenSequenceItem(tween: Tween(begin: 6, end: 0), weight: 1),
    ]).animate(_shakeController);
    _startTimer();
  }

  /// Disposes controllers, focus nodes, and the shake controller.
  @override
  void dispose() {
    for (final c in _controllers) {
      c.dispose();
    }
    for (final f in _focusNodes) {
      f.dispose();
    }
    _shakeController.dispose();
    super.dispose();
  }

  /// Starts a 59-second countdown timer before the user can resend the code.
  void _startTimer() {
    _canResend = false;
    _timerSeconds = 59;
    Future.doWhile(() async {
      await Future.delayed(const Duration(seconds: 1));
      if (!mounted) return false;
      if (_timerSeconds > 0) {
        setState(() => _timerSeconds--);
        return true;
      }
      setState(() => _canResend = true);
      return false;
    });
  }

  /// Returns the concatenated OTP digits from all input fields.
  String get _otp => _controllers.map((c) => c.text).join();

  /// Whether all 6 OTP digits have been entered.
  bool get _isComplete => _otp.length == 6;

  /// Handles text changes in OTP fields, auto-advancing focus or
  /// handling paste of a full code.
  void _onChanged(int i, String v) {
    if (v.length > 1) {
      final digits = v
          .split('')
          .where((d) => RegExp(r'[0-9]').hasMatch(d))
          .take(6)
          .toList();
      for (int j = 0; j < 6; j++) {
        _controllers[j].text = j < digits.length ? digits[j] : '';
      }
      if (digits.length == 6) {
        _focusNodes[5].unfocus();
      } else if (digits.isNotEmpty) {
        _focusNodes[digits.length].requestFocus();
      }
      setState(() {});
      return;
    }
    if (v.isNotEmpty) {
      if (i < 5) {
        _focusNodes[i + 1].requestFocus();
      } else {
        _focusNodes[i].unfocus();
      }
    } else if (i > 0) {
      _focusNodes[i - 1].requestFocus();
    }
    setState(() {});
  }

  /// Verifies the OTP code with the server and navigates to the home screen.
  Future<void> _verify() async {
    if (!_isComplete) return;
    setState(() => _isLoading = true);
    try {
      final auth = context.read<AuthProvider>();
      final success = await auth.verifyOtp(widget.phone, _otp);
      if (!mounted) return;
      if (success) {
        if (auth.isTechnician) {
          context.go(AppRoutes.technicianHome);
        } else {
          context.go(AppRoutes.tenantHome);
        }
      } else {
        _shakeController.forward(from: 0);
        for (final c in _controllers) {
          c.text = '';
        }
        _focusNodes[0].requestFocus();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              AppLocalizations.of(context)!.invalidOtp,
              style: const TextStyle(fontFamily: 'Cairo'),
            ),
            backgroundColor: MaskanColors.kBgCard,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  /// Resends the OTP code to the user's phone and restarts the timer.
  Future<void> _resend() async {
    final auth = context.read<AuthProvider>();
    final success = await auth.sendOtp(widget.phone);
    if (mounted) {
      if (success) {
        _startTimer();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              AppLocalizations.of(context)!.otpResent,
              style: const TextStyle(fontFamily: 'Cairo'),
            ),
            backgroundColor: MaskanColors.kBgCard,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  /// Formats the phone number with a masked middle section for display.
  String get _formattedPhone {
    final p = widget.phone;
    if (p.length >= 10) {
      return '+218 ${p.substring(0, 2)}X XXX ${p.substring(p.length - 4)}';
    }
    return '+218 $p';
  }

  /// Builds the OTP verification screen with a code input, resend timer,
  /// and verification button.
  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios, color: context.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text(
          AppLocalizations.of(context)!.verifyOtp,
          style: TextStyle(color: context.textPrimary, fontFamily: 'Cairo'),
        ),
        centerTitle: true,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: MaskanColors.kBlue.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(color: MaskanColors.kBlue, width: 0.5),
                ),
                child: const Icon(
                  Icons.message_outlined,
                  color: MaskanColors.kBlue,
                  size: 40,
                ),
              ),
              const SizedBox(height: 20),
              Text(
                AppLocalizations.of(context)!.verifyPhoneTitle,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w600,
                  color: context.textPrimary,
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 8),
              Text(
                AppLocalizations.of(context)!.otpSentTo,
                style: TextStyle(
                  fontSize: 14,
                  color: context.textSecondary,
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 4),
              Text(
                _formattedPhone,
                style: const TextStyle(
                  fontSize: 15,
                  color: MaskanColors.kGold,
                  fontWeight: FontWeight.w600,
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 4),
              TextButton(
                onPressed: () => context.pop(),
                child: Text(
                  AppLocalizations.of(context)!.changeNumber,
                  style: const TextStyle(
                    fontSize: 13,
                    color: MaskanColors.kBlue,
                    fontFamily: 'Cairo',
                  ),
                ),
              ),
              const SizedBox(height: 32),
              AnimatedBuilder(
                animation: _shakeAnimation,
                builder: (_, a) => Transform.translate(
                  offset: Offset(_shakeAnimation.value, 0),
                  child: Directionality(
                    textDirection: TextDirection.ltr,
                    child: LayoutBuilder(
                      builder: (_, constraints) {
                        final boxSize = ((constraints.maxWidth - 36) / 6).clamp(
                          36.0,
                          54.0,
                        );
                        return Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(6, (i) {
                            final filled = _controllers[i].text.isNotEmpty;
                            return Container(
                              width: boxSize,
                              height: boxSize + 8,
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              decoration: BoxDecoration(
                                color: MaskanColors.kBgInput,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: filled
                                      ? MaskanColors.kBlue.withValues(
                                          alpha: 0.3,
                                        )
                                      : _shakeController.isAnimating
                                      ? MaskanColors.kDanger
                                      : MaskanColors.kGlassBorder,
                                  width: 1.5,
                                ),
                              ),
                              child: TextField(
                                controller: _controllers[i],
                                focusNode: _focusNodes[i],
                                textAlign: TextAlign.center,
                                keyboardType: TextInputType.number,
                                maxLength: 1,
                                style: TextStyle(
                                  fontSize: boxSize > 48 ? 24 : 20,
                                  fontWeight: FontWeight.bold,
                                  color: context.textPrimary,
                                  fontFamily: 'Cairo',
                                ),
                                decoration: const InputDecoration(
                                  counterText: '',
                                  border: InputBorder.none,
                                  enabledBorder: InputBorder.none,
                                  focusedBorder: InputBorder.none,
                                ),
                                onChanged: (v) => _onChanged(i, v),
                              ),
                            );
                          }),
                        );
                      },
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Consumer<AuthProvider>(
                builder: (_, auth, _) {
                  if (auth.lastOtp == null) return const SizedBox.shrink();
                  return Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(
                      color: MaskanColors.kSuccess.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: MaskanColors.kSuccess.withValues(alpha: 0.3),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.info_outline,
                          color: MaskanColors.kSuccess,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'رمز التحقق: ${auth.lastOtp}',
                          style: const TextStyle(
                            color: MaskanColors.kSuccess,
                            fontWeight: FontWeight.w600,
                            fontSize: 16,
                            fontFamily: 'Cairo',
                            letterSpacing: 2,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
              _canResend
                  ? TextButton(
                      onPressed: _resend,
                      child: Text(
                        AppLocalizations.of(context)!.resendCode,
                        style: const TextStyle(
                          color: MaskanColors.kBlue,
                          fontFamily: 'Cairo',
                        ),
                      ),
                    )
                  : Text(
                      '${AppLocalizations.of(context)!.resendIn} 00:${_timerSeconds.toString().padLeft(2, '0')}',
                      style: TextStyle(
                        color: context.textSecondary,
                        fontFamily: 'Cairo',
                        fontSize: 13,
                      ),
                    ),
              const SizedBox(height: 20),
              PrimaryButton(
                label: AppLocalizations.of(context)!.confirm,
                isLoading: _isLoading,
                onPressed: _isComplete ? _verify : null,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
