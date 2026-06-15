import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../config/colors.dart';
import '../../l10n/app_localizations.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';

/// شاشة نسيت كلمة المرور — يدخل المستخدم رقم هاتفه لاستلام رمز OTP لإعادة تعيين كلمة المرور
class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

/// حالة [ForgotPasswordScreen] التي تدير إدخال رقم الهاتف وإرسال OTP
class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _phoneController = TextEditingController();
  bool _isLoading = false;

  /// Disposes the phone text controller.
  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  /// Sends an OTP to the entered phone number and navigates to the
  /// reset password screen on success.
  Future<void> _sendOtp() async {
    final phone = _phoneController.text.trim();
    if (phone.isEmpty) return;
    if (!RegExp(r'^09[12348]\d{7}$').hasMatch(phone)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)!.phoneInvalidLibyan, style: const TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kBgCard,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    setState(() => _isLoading = true);
    try {
      final auth = context.read<AuthProvider>();
      final success = await auth.sendOtp(_phoneController.text.trim());
      if (!mounted) return;
      if (success) {
        context.go(AppRoutes.resetPassword, extra: _phoneController.text.trim());
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(auth.error ?? AppLocalizations.of(context)!.failedToSendCode, style: const TextStyle(fontFamily: 'Cairo')),
            backgroundColor: MaskanColors.kBgCard,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  /// Builds the forgot password screen with phone input and send code button.
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
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
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
                      width: 72, height: 72,
                      decoration: BoxDecoration(
                        color: const Color(0xFFC49A2B).withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFFC49A2B), width: 0.5),
                      ),
                      child: const Icon(Icons.lock_open, color: Color(0xFFC49A2B), size: 36),
                    ),
                    const SizedBox(height: 20),
                    Text(
                      loc.forgotPasswordTitle,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1A1D22),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      loc.enterPhoneToReset,
                      style: const TextStyle(
                        fontSize: 14,
                        color: Color(0xFF989EA7),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 28),
                    TextField(
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      maxLength: 10,
                      textDirection: TextDirection.rtl,
                      style: const TextStyle(
                        fontSize: 15,
                        color: Color(0xFF2C3138),
                        fontFamily: 'Cairo',
                      ),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.white,
                        hintText: loc.phoneNumber,
                        hintStyle: const TextStyle(
                          color: Color(0xFF989EA7),
                          fontFamily: 'Cairo',
                        ),
                        prefixIcon: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const SizedBox(width: 12),
                            Text(
                              loc.countryCode,
                              style: const TextStyle(
                                color: Color(0xFFC49A2B), fontSize: 15,
                                fontFamily: 'Cairo', fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(width: 8),
                            const VerticalDivider(color: Color(0xFFEDEEF0), width: 1),
                          ],
                        ),
                        suffixIcon: const Icon(Icons.phone_android, color: Color(0xFF989EA7), size: 20),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(color: Color(0xFFEDEEF0), width: 1.5),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(color: Color(0xFFEDEEF0), width: 1.5),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: const BorderSide(color: Color(0xFF1a3a5c), width: 1.5),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _sendOtp,
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
                                width: 22, height: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.5,
                                  color: Colors.white,
                                ),
                              )
                            : Text(loc.sendCode),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        TextButton.icon(
                          onPressed: () => context.go(AppRoutes.login),
                          icon: const Icon(Icons.arrow_back, size: 16, color: Color(0xFF1a3a5c)),
                          label: Text(
                            loc.login,
                            style: const TextStyle(
                              fontSize: 13,
                              color: Color(0xFF1a3a5c),
                              fontFamily: 'Cairo',
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
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
