import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../l10n/app_localizations.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';

/// شاشة إعادة تعيين كلمة المرور — يدخل المستخدم رمز OTP وكلمة مرور جديدة مع التأكيد
class ResetPasswordScreen extends StatefulWidget {
  /// The phone number used to request the password reset.
  final String phone;
  const ResetPasswordScreen({super.key, required this.phone});

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

/// حالة [ResetPasswordScreen] — إدارة إدخال OTP وحقول كلمة المرور الجديدة ومؤشر قوتها
class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final List<TextEditingController> _otpControllers = List.generate(
    6,
    (_) => TextEditingController(),
  );
  final List<FocusNode> _otpFocusNodes = List.generate(6, (_) => FocusNode());
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();
  bool _obscurePass = true;
  bool _obscureConfirm = true;
  bool _isLoading = false;

  /// Disposes all controllers and focus nodes.
  @override
  void dispose() {
    for (final c in _otpControllers) {
      c.dispose();
    }
    for (final f in _otpFocusNodes) {
      f.dispose();
    }
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  /// Returns the concatenated OTP digits.
  String get _otp => _otpControllers.map((c) => c.text).join();

  /// Whether all 6 OTP digits have been entered.
  bool get _otpComplete => _otp.length == 6;

  /// Computes a password strength score from 0 to 4.
  int get _passwordStrength {
    final p = _passwordController.text;
    if (p.isEmpty) return 0;
    int s = 0;
    if (p.length >= 6) s++;
    if (p.length >= 10) s++;
    if (RegExp(r'[A-Za-z]').hasMatch(p) && RegExp(r'[0-9]').hasMatch(p)) s++;
    if (RegExp(r'[!@#$%^&*(),.?":{}|<>]').hasMatch(p)) s++;
    return s;
  }

  /// Returns a localized label for the current password strength.
  String _strengthLabel(BuildContext context) {
    final s = _passwordStrength;
    if (s <= 1) return AppLocalizations.of(context)!.weak;
    if (s == 2) return AppLocalizations.of(context)!.medium;
    if (s == 3) return AppLocalizations.of(context)!.good;
    return AppLocalizations.of(context)!.strong;
  }

  /// Returns a color corresponding to the current password strength.
  Color get _strengthColor {
    final s = _passwordStrength;
    if (s <= 1) return MaskanColors.kDanger;
    if (s == 2) return MaskanColors.kWarning;
    if (s == 3) return MaskanColors.kBlue;
    return MaskanColors.kSuccess;
  }

  /// Handles text changes in OTP fields, auto-advancing focus or
  /// handling paste of a full code.
  void _onOtpChanged(int i, String v) {
    if (v.length > 1) {
      final digits = v
          .split('')
          .where((d) => RegExp(r'[0-9]').hasMatch(d))
          .take(6)
          .toList();
      for (int j = 0; j < 6; j++) {
        _otpControllers[j].text = j < digits.length ? digits[j] : '';
      }
      if (digits.length == 6) {
        _otpFocusNodes[5].unfocus();
      } else if (digits.isNotEmpty) {
        _otpFocusNodes[digits.length].requestFocus();
      }
      setState(() {});
      return;
    }
    if (v.isNotEmpty) {
      if (i < 5) {
        _otpFocusNodes[i + 1].requestFocus();
      } else {
        _otpFocusNodes[i].unfocus();
      }
    } else if (i > 0) {
      _otpFocusNodes[i - 1].requestFocus();
    }
    setState(() {});
  }

  /// Validates inputs and submits the password reset request to the server.
  Future<void> _reset() async {
    if (!_otpComplete || _passwordController.text.length < 6) return;
    if (_passwordController.text != _confirmController.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            AppLocalizations.of(context)!.passwordMismatch,
            style: const TextStyle(fontFamily: 'Cairo'),
          ),
          backgroundColor: MaskanColors.kBgCard,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    setState(() => _isLoading = true);
    try {
      final auth = context.read<AuthProvider>();
      final success = await auth.resetPassword(
        widget.phone,
        _otp,
        _passwordController.text,
      );
      if (!mounted) return;
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              AppLocalizations.of(context)!.passwordChangedSuccess,
              style: const TextStyle(fontFamily: 'Cairo'),
            ),
            backgroundColor: MaskanColors.kSuccess.withValues(alpha: 0.9),
            behavior: SnackBarBehavior.floating,
          ),
        );
        context.go(AppRoutes.login);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              auth.error ?? AppLocalizations.of(context)!.resetFailed,
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

  /// Builds the reset password screen with OTP input, new password fields,
  /// strength indicator, and update button.
  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context)!;

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF0a1628),
              Color(0xFF13294b),
              Color(0xFF1a3a5c),
              Color(0xFF0f2438),
              Color(0xFF0a1628),
            ],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: Container(
                constraints: const BoxConstraints(maxWidth: 400),
                padding: const EdgeInsets.symmetric(
                  horizontal: 28,
                  vertical: 32,
                ),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x4D000000),
                      blurRadius: 60,
                      offset: Offset(0, 20),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
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
                    const SizedBox(height: 20),
                    Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        color: const Color(0xFF1a3a5c).withValues(alpha: 0.08),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.shield_outlined,
                        color: Color(0xFF1a3a5c),
                        size: 36,
                      ),
                    ),
                    const SizedBox(height: 20),
                    Text(
                      loc.resetPassword,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A1D22),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      loc.otpSentToYourNumber,
                      style: const TextStyle(
                        fontSize: 14,
                        color: Color(0xFF989EA7),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 16),
                    Directionality(
                      textDirection: TextDirection.ltr,
                      child: LayoutBuilder(
                        builder: (_, constraints) {
                          final boxSize = ((constraints.maxWidth - 36) / 6)
                              .clamp(34.0, 48.0);
                          return Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: List.generate(6, (i) {
                              final filled = _otpControllers[i].text.isNotEmpty;
                              return Container(
                                width: boxSize,
                                height: boxSize + 8,
                                margin: const EdgeInsets.symmetric(
                                  horizontal: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(
                                    color: filled
                                        ? const Color(
                                            0xFF1a3a5c,
                                          ).withValues(alpha: 0.3)
                                        : const Color(0xFFEDEEF0),
                                    width: 1.5,
                                  ),
                                ),
                                child: TextField(
                                  controller: _otpControllers[i],
                                  focusNode: _otpFocusNodes[i],
                                  textAlign: TextAlign.center,
                                  keyboardType: TextInputType.number,
                                  maxLength: 1,
                                  style: TextStyle(
                                    fontSize: boxSize > 44 ? 20 : 16,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF2C3138),
                                    fontFamily: 'Cairo',
                                  ),
                                  decoration: const InputDecoration(
                                    counterText: '',
                                    border: InputBorder.none,
                                    enabledBorder: InputBorder.none,
                                    focusedBorder: InputBorder.none,
                                  ),
                                  onChanged: (v) => _onOtpChanged(i, v),
                                ),
                              );
                            }),
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 12),
                    Consumer<AuthProvider>(
                      builder: (_, auth, _) {
                        if (auth.lastOtp == null)
                          return const SizedBox.shrink();
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              color: MaskanColors.kSuccess.withValues(
                                alpha: 0.1,
                              ),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: MaskanColors.kSuccess.withValues(
                                  alpha: 0.3,
                                ),
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
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 12),
                    Text(
                      loc.newPassword,
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF989EA7),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _passwordController,
                      obscureText: _obscurePass,
                      textDirection: TextDirection.rtl,
                      style: const TextStyle(
                        fontSize: 15,
                        color: Color(0xFF2C3138),
                        fontFamily: 'Cairo',
                      ),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        hintText: loc.password,
                        hintStyle: const TextStyle(
                          color: Color(0xFF989EA7),
                          fontFamily: 'Cairo',
                        ),
                        prefixIcon: const Icon(
                          Icons.lock_outline,
                          color: Color(0xFF989EA7),
                          size: 20,
                        ),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePass
                                ? Icons.visibility_off
                                : Icons.visibility,
                            color: const Color(0xFF989EA7),
                            size: 20,
                          ),
                          onPressed: () =>
                              setState(() => _obscurePass = !_obscurePass),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 14,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFFEDEEF0),
                            width: 1.5,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFFEDEEF0),
                            width: 1.5,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFF1a3a5c),
                            width: 1.5,
                          ),
                        ),
                      ),
                      onChanged: (_) => setState(() {}),
                    ),
                    if (_passwordController.text.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(2),
                        child: LinearProgressIndicator(
                          value: _passwordStrength / 4,
                          backgroundColor: const Color(0xFFF0F0F0),
                          valueColor: AlwaysStoppedAnimation<Color>(
                            _strengthColor,
                          ),
                          minHeight: 4,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${AppLocalizations.of(context)!.passwordStrength}${_strengthLabel(context)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: _strengthColor,
                          fontFamily: 'Cairo',
                        ),
                      ),
                    ],
                    const SizedBox(height: 16),
                    Text(
                      loc.confirmPassword,
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF989EA7),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _confirmController,
                      obscureText: _obscureConfirm,
                      textDirection: TextDirection.rtl,
                      style: const TextStyle(
                        fontSize: 15,
                        color: Color(0xFF2C3138),
                        fontFamily: 'Cairo',
                      ),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        hintText: loc.confirmPassword,
                        hintStyle: const TextStyle(
                          color: Color(0xFF989EA7),
                          fontFamily: 'Cairo',
                        ),
                        prefixIcon: const Icon(
                          Icons.lock_outline,
                          color: Color(0xFF989EA7),
                          size: 20,
                        ),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscureConfirm
                                ? Icons.visibility_off
                                : Icons.visibility,
                            color: const Color(0xFF989EA7),
                            size: 20,
                          ),
                          onPressed: () => setState(
                            () => _obscureConfirm = !_obscureConfirm,
                          ),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 14,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFFEDEEF0),
                            width: 1.5,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFFEDEEF0),
                            width: 1.5,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(
                            color: Color(0xFF1a3a5c),
                            width: 1.5,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _reset,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1a3a5c),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                          elevation: 0,
                          textStyle: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            fontFamily: 'Cairo',
                          ),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.5,
                                  color: Colors.white,
                                ),
                              )
                            : Text(loc.updatePassword),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
