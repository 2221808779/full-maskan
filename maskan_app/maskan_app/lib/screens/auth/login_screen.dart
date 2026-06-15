import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تسجيل الدخول — يمكن للمستخدم المصادقة باستخدام رقم الهاتف وكلمة المرور
/// مع روابط للتسجيل الجديد واستعادة كلمة المرور
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

/// حالة [LoginScreen] التي تدير حقول النموذج ومنطق تسجيل الدخول
class _LoginScreenState extends State<LoginScreen> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  /// Disposes the text editing controllers.
  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  /// Attempts to log the user in via [AuthProvider] and navigates to the
  /// appropriate home screen on success.
  Future<void> _login() async {
    final phone = _phoneController.text.trim();
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
    final auth = context.read<AuthProvider>();
    final success = await auth.login(
      phone,
      _passwordController.text,
    );
    if (!mounted) return;
    if (success) {
      if (auth.isTechnician) {
        context.go(AppRoutes.technicianHome);
      } else {
        context.go(AppRoutes.tenantHome);
      }
    } else if (auth.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error!, style: const TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kBgCard,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  /// Builds the login form with phone/password fields, login button,
  /// and links to registration and password recovery.
  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final isLoading = context.watch<AuthProvider>().isLoading;

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
                width: 400,
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
                    const SizedBox(height: 4),
                    Text(
                      loc.platformTitle,
                      style: const TextStyle(
                        fontSize: 14,
                        color: Color(0xFF989EA7),
                        fontFamily: 'Cairo',
                      ),
                    ),
                    const SizedBox(height: 32),
                    _buildInput(
                      controller: _phoneController,
                      hint: loc.phoneNumber,
                      icon: Icons.phone_rounded,
                      keyboardType: TextInputType.phone,
                      maxLength: 10,
                    ),
                    const SizedBox(height: 16),
                    _buildInput(
                      controller: _passwordController,
                      hint: loc.password,
                      icon: Icons.lock_rounded,
                      obscureText: _obscurePassword,
                      suffix: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                          color: const Color(0xFF989EA7),
                          size: 20,
                        ),
                        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                    ),
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: () => context.go(AppRoutes.forgotPassword),
                        style: TextButton.styleFrom(padding: EdgeInsets.zero),
                        child: Text(
                          loc.forgotPassword,
                          style: const TextStyle(
                            fontSize: 13,
                            color: Color(0xFF1a3a5c),
                            fontFamily: 'Cairo',
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: isLoading ? null : _login,
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
                        child: isLoading
                            ? const SizedBox(
                                width: 22, height: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.5,
                                  color: Colors.white,
                                ),
                              )
                            : Text(loc.login),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        const Expanded(child: Divider(color: Color(0xFFEDEEF0))),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            loc.or,
                            style: const TextStyle(
                              fontSize: 13,
                              color: Color(0xFF989EA7),
                              fontFamily: 'Cairo',
                            ),
                          ),
                        ),
                        const Expanded(child: Divider(color: Color(0xFFEDEEF0))),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          loc.noAccount,
                          style: const TextStyle(
                            fontSize: 13,
                            color: Color(0xFF989EA7),
                            fontFamily: 'Cairo',
                          ),
                        ),
                        const SizedBox(width: 4),
                        GestureDetector(
                          onTap: () => context.go(AppRoutes.register),
                          child: Text(
                            loc.createAccount,
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1a3a5c),
                              fontFamily: 'Cairo',
                              decoration: TextDecoration.underline,
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

  /// Builds a styled text input field with an icon and optional password visibility toggle.
  Widget _buildInput({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType? keyboardType,
    bool obscureText = false,
    Widget? suffix,
    int? maxLength,
  }) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      maxLength: maxLength,
      textDirection: TextDirection.rtl,
      style: const TextStyle(
        fontSize: 15,
        color: Color(0xFF2C3138),
        fontFamily: 'Cairo',
      ),
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.white,
        hintText: hint,
        hintStyle: const TextStyle(
          color: Color(0xFF989EA7),
          fontFamily: 'Cairo',
        ),
        prefixIcon: Icon(icon, color: const Color(0xFF989EA7), size: 20),
        suffixIcon: suffix,
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
    );
  }
}
