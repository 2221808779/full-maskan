/// شاشة إنشاء حساب جديد — تدعم تسجيل المستأجرين والفنيين مع إدخال الاسم والهاتف وكلمة المرور والتخصصات
import 'package:flutter/material.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../core/utils/helpers.dart';
import '../../providers/auth_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تسجيل حساب جديد —用户可以 التسجيل كمستأجر أو فني مع التحقق عبر OTP
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

/// حالة [RegisterScreen] — إدارة نموذج التسجيل متعدد الخطوات
/// user type selection, specialties picker, and OTP verification.
class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();
  final _experienceController = TextEditingController();
  bool _obscurePass = true;
  bool _obscureConfirm = true;
  bool _agreeTerms = false;
  String _selectedType = 'tenant';
  bool _showOtp = false;
  final _otpController = TextEditingController();

  List<Map<String, dynamic>> _specialties = [];
  Set<int> _selectedSpecialties = {};
  bool _loadingSpecialties = false;

  /// Disposes all text editing controllers.
  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    _experienceController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  /// Computes a password strength score from 0 (empty) to 4 (strong).
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

  /// Loads the list of available technician specialties from the API.
  Future<void> _loadSpecialties() async {
    if (_specialties.isNotEmpty) return;
    setState(() => _loadingSpecialties = true);
    final auth = context.read<AuthProvider>();
    final list = await auth.getSpecialties();
    if (!mounted) return;
    setState(() {
      _specialties = list;
      _loadingSpecialties = false;
    });
  }

  /// Validates the form and submits registration data via [AuthProvider].
  /// On success, switches to the OTP verification step.
  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedType == 'technician' && _selectedSpecialties.isEmpty) {
      Helpers.showSnackBar(context, 'يرجى اختيار تخصص واحد على الأقل', isError: true);
      return;
    }
    if (!_agreeTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).pleaseAgreeTerms,
            style: const TextStyle(fontFamily: 'Cairo')),
          backgroundColor: const Color(0xFF0D1E2C),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    final auth = context.read<AuthProvider>();
    final specializations = _selectedType == 'technician' && _selectedSpecialties.isNotEmpty
        ? _selectedSpecialties.toList()
        : null;
    final success = await auth.register(
      name: _nameController.text.trim(),
      phone: _phoneController.text.trim(),
      password: _passwordController.text,
      userType: _selectedType,
      specializations: specializations,
      experienceYears: _selectedType == 'technician' && _experienceController.text.isNotEmpty
          ? int.tryParse(_experienceController.text)
          : null,
    );
    if (!mounted) return;
    if (success) {
      setState(() => _showOtp = true);
    } else if (auth.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error!, style: const TextStyle(fontFamily: 'Cairo')),
          backgroundColor: const Color(0xFF0D1E2C),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  /// Verifies the OTP code entered by the user and navigates to the home screen.
  Future<void> _verifyOtp() async {
    final auth = context.read<AuthProvider>();
    final success = await auth.verifyOtp(
      _phoneController.text.trim(),
      _otpController.text,
    );
    if (!mounted) return;
    if (success) {
      context.go(AppRoutes.tenantHome);
    } else if (auth.error != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error!, style: const TextStyle(fontFamily: 'Cairo')),
          backgroundColor: const Color(0xFF0D1E2C),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  /// Builds the registration screen with either the registration form
  /// or the OTP verification form based on [state].
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
                    const SizedBox(height: 28),
                    if (!_showOtp) _buildRegisterForm(loc, isLoading) else _buildOtpForm(loc, isLoading),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  /// Builds the registration form with role selection, personal info fields,
  /// password strength indicator, and terms agreement checkbox.
  Widget _buildRegisterForm(AppLocalizations loc, bool isLoading) {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          Row(
            children: [
              Expanded(child: _buildRoleBtn(loc.tenant, 'tenant')),
              const SizedBox(width: 8),
              Expanded(child: _buildRoleBtn(loc.technician, 'technician')),
            ],
          ),
          if (_selectedType == 'technician') ...[
            const SizedBox(height: 16),
            _buildSpecializationsPicker(),
            const SizedBox(height: 16),
            _buildLabel(loc.yearsOfExperience),
            const SizedBox(height: 6),
            _buildTextField(
              controller: _experienceController,
              hint: loc.years,
              icon: Icons.work_history_rounded,
              keyboardType: TextInputType.number,
            ),
          ],
          const SizedBox(height: 24),
          _buildLabel(loc.fullName),
          const SizedBox(height: 6),
          _buildTextField(
            controller: _nameController,
            hint: loc.fullName,
            icon: Icons.person_outline_rounded,
            validator: (v) => v == null || v.trim().isEmpty ? loc.enterName : null,
          ),
          const SizedBox(height: 16),
          _buildLabel(loc.phoneNumber),
          const SizedBox(height: 6),
          _buildTextField(
            controller: _phoneController,
            hint: loc.phoneNumber,
            maxLength: 10,
            icon: Icons.phone_rounded,
            keyboardType: TextInputType.phone,
            prefix: const Padding(
              padding: EdgeInsets.only(left: 4),
              child: Text('+218', style: TextStyle(
                color: Color(0xFFC49A2B),
                fontWeight: FontWeight.w600,
                fontFamily: 'Cairo',
                fontSize: 14,
              )),
            ),
            validator: (v) {
              if (v == null || v.trim().isEmpty) return loc.enterPhone;
              if (!RegExp(r'^09[12348]\d{7}$').hasMatch(v.trim())) return loc.phoneInvalidLibyan;
              return null;
            },
          ),
          const SizedBox(height: 16),
          _buildLabel(loc.password),
          const SizedBox(height: 6),
          _buildTextField(
            controller: _passwordController,
            hint: '••••••••',
            icon: Icons.lock_outline_rounded,
            obscureText: _obscurePass,
            suffix: IconButton(
              icon: Icon(
                _obscurePass ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                color: const Color(0xFF989EA7), size: 20,
              ),
              onPressed: () => setState(() => _obscurePass = !_obscurePass),
            ),
            onChanged: (_) => setState(() {}),
            validator: (v) => v == null || v.length < 6 ? loc.passwordTooShort : null,
          ),
          if (_passwordController.text.isNotEmpty) ...[
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(2),
              child: LinearProgressIndicator(
                value: _passwordStrength / 4,
                backgroundColor: const Color(0xFFEDEEF0),
                valueColor: AlwaysStoppedAnimation<Color>(_strengthColor),
                minHeight: 4,
              ),
            ),
          ],
          const SizedBox(height: 16),
          _buildLabel(loc.confirmPassword),
          const SizedBox(height: 6),
          _buildTextField(
            controller: _confirmController,
            hint: '••••••••',
            icon: Icons.lock_outline_rounded,
            obscureText: _obscureConfirm,
            suffix: IconButton(
              icon: Icon(
                _obscureConfirm ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                color: const Color(0xFF989EA7), size: 20,
              ),
              onPressed: () => setState(() => _obscureConfirm = !_obscureConfirm),
            ),
            validator: (v) => v != _passwordController.text ? loc.passwordMismatch : null,
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              SizedBox(
                height: 22, width: 22,
                child: Checkbox(
                  value: _agreeTerms,
                  onChanged: (v) => setState(() => _agreeTerms = v ?? false),
                  activeColor: const Color(0xFF1a3a5c),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                  side: const BorderSide(color: Color(0xFFDDE0E4)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: RichText(
                  text: TextSpan(
                    children: [
                      TextSpan(
                        text: loc.agreeTerms,
                        style: const TextStyle(
                          fontFamily: 'Cairo', fontSize: 12,
                          color: Color(0xFF989EA7),
                        ),
                      ),
                      const WidgetSpan(child: SizedBox(width: 4)),
                      TextSpan(
                        text: loc.termsAndConditions,
                        style: const TextStyle(
                          fontFamily: 'Cairo', fontSize: 12,
                          color: Color(0xFF1a3a5c),
                          decoration: TextDecoration.underline,
                          fontWeight: FontWeight.w600,
                        ),
                        recognizer: TapGestureRecognizer()
                          ..onTap = () async {
                            final agreed = await context.push<bool>(AppRoutes.terms);
                            if (agreed == true && mounted) {
                              setState(() => _agreeTerms = true);
                            }
                          },
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: isLoading ? null : _register,
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
                        strokeWidth: 2.5, color: Colors.white,
                      ),
                    )
                  : Text(loc.registerNewAccount),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              const Expanded(child: Divider(color: Color(0xFFEDEEF0))),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(loc.or, style: const TextStyle(
                  fontSize: 13, color: Color(0xFF989EA7), fontFamily: 'Cairo',
                )),
              ),
              const Expanded(child: Divider(color: Color(0xFFEDEEF0))),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(loc.haveAccount, style: const TextStyle(
                fontSize: 13, color: Color(0xFF989EA7), fontFamily: 'Cairo',
              )),
              const SizedBox(width: 4),
              GestureDetector(
                onTap: () => context.go(AppRoutes.login),
                child: Text(loc.login, style: const TextStyle(
                  fontSize: 13, fontWeight: FontWeight.w700,
                  color: Color(0xFF1a3a5c), fontFamily: 'Cairo',
                  decoration: TextDecoration.underline,
                )),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Builds the OTP verification form shown after successful registration.
  Widget _buildOtpForm(AppLocalizations loc, bool isLoading) {
    return Column(
      children: [
        const Text('✓', style: TextStyle(fontSize: 40, color: Color(0xFFC49A2B))),
        const SizedBox(height: 8),
        Text(loc.accountCreated, style: const TextStyle(
          fontSize: 18, fontWeight: FontWeight.w700,
          color: Color(0xFF1A1D22), fontFamily: 'Cairo',
        )),
        const SizedBox(height: 4),
        Text(loc.otpSentTo, style: const TextStyle(
          fontSize: 13, color: Color(0xFF989EA7), fontFamily: 'Cairo',
        )),
        const SizedBox(height: 4),
        Text(_phoneController.text, style: const TextStyle(
          fontSize: 14, fontWeight: FontWeight.w700,
          color: Color(0xFFC49A2B), fontFamily: 'Cairo',
        )),
        const SizedBox(height: 24),
        _buildLabel(loc.verificationCode),
        const SizedBox(height: 6),
        TextField(
          controller: _otpController,
          textAlign: TextAlign.center,
          maxLength: 6,
          keyboardType: TextInputType.number,
          style: const TextStyle(
            fontSize: 24, letterSpacing: 8,
            color: Color(0xFF2C3138), fontFamily: 'Cairo',
          ),
          decoration: InputDecoration(
            counterText: '',
            filled: true,
            fillColor: Colors.white,
            hintText: '------',
            hintStyle: const TextStyle(
              color: Color(0xFFDDE0E4), fontSize: 24, letterSpacing: 8,
            ),
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
            onPressed: isLoading ? null : _verifyOtp,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1a3a5c),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
              elevation: 0,
              textStyle: const TextStyle(
                fontSize: 16, fontWeight: FontWeight.w700, fontFamily: 'Cairo',
              ),
            ),
            child: isLoading
                ? const SizedBox(
                    width: 22, height: 22,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5, color: Colors.white,
                    ),
                  )
                : Text(loc.confirmCode),
          ),
        ),
      ],
    );
  }

  /// Builds a wrap of filter chips for selecting technician specialties.
  Widget _buildSpecializationsPicker() {
    if (_loadingSpecialties) {
      return const Center(child: Padding(
        padding: EdgeInsets.all(8),
        child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
      ));
    }
    if (_specialties.isEmpty) return const SizedBox.shrink();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildLabel('التخصصات'),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: _specialties.map((s) {
            final id = s['id'] as int;
            final name = s['display_name'] as String? ?? s['name'] as String;
            final selected = _selectedSpecialties.contains(id);
            return FilterChip(
              label: Text(name, style: TextStyle(
                fontSize: 13,
                fontFamily: 'Cairo',
                color: selected ? Colors.white : const Color(0xFF6A717B),
              )),
              selected: selected,
              onSelected: (v) {
                setState(() {
                  if (v) {
                    _selectedSpecialties.add(id);
                  } else {
                    _selectedSpecialties.remove(id);
                  }
                });
              },
              selectedColor: const Color(0xFF1a3a5c),
              checkmarkColor: Colors.white,
              backgroundColor: Colors.white,
              side: BorderSide(
                color: selected ? const Color(0xFF1a3a5c) : const Color(0xFFEDEEF0),
                width: 1.5,
              ),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
            );
          }).toList(),
        ),
      ],
    );
  }

  /// Builds a selectable role button for tenant or technician.
  Widget _buildRoleBtn(String label, String type) {
    final selected = _selectedType == type;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedType = type;
          if (type == 'technician') _selectedSpecialties = {};
        });
        if (type == 'technician') _loadSpecialties();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: selected ? const Color(0xFF1a3a5c) : Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: selected ? const Color(0xFF1a3a5c) : const Color(0xFFEDEEF0),
            width: 1.5,
          ),
        ),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: selected ? Colors.white : const Color(0xFF6A717B),
            fontFamily: 'Cairo',
          ),
        ),
      ),
    );
  }

  /// Builds a right-aligned label text for form fields.
  Widget _buildLabel(String text) {
    return Align(
      alignment: Alignment.centerRight,
      child: Text(text, style: const TextStyle(
        fontSize: 13, fontWeight: FontWeight.w500,
        color: Color(0xFF6A717B), fontFamily: 'Cairo',
      )),
    );
  }

  /// Builds a styled text form field with icon, optional prefix/suffix,
  /// and validation support.
  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType? keyboardType,
    bool obscureText = false,
    Widget? suffix,
    Widget? prefix,
    int? maxLength,
    String? Function(String?)? validator,
    void Function(String)? onChanged,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      maxLength: maxLength,
      textDirection: TextDirection.rtl,
      validator: validator,
      onChanged: onChanged,
      style: const TextStyle(
        fontSize: 15, color: Color(0xFF2C3138), fontFamily: 'Cairo',
      ),
      decoration: InputDecoration(
        filled: true,
        fillColor: Colors.white,
        hintText: hint,
        hintStyle: const TextStyle(
          color: Color(0xFF989EA7), fontFamily: 'Cairo',
        ),
        prefixIcon: prefix != null
            ? Row(mainAxisSize: MainAxisSize.min, children: [
                const SizedBox(width: 12),
                prefix,
                const SizedBox(width: 8),
                const VerticalDivider(color: Color(0xFFEDEEF0), width: 1),
              ])
            : Icon(icon, color: const Color(0xFF989EA7), size: 20),
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

  /// Returns a color corresponding to the current password strength.
  Color get _strengthColor {
    final s = _passwordStrength;
    if (s <= 1) return const Color(0xFFC0392B);
    if (s == 2) return const Color(0xFFF0A500);
    if (s == 3) return const Color(0xFF1a3a5c);
    return const Color(0xFF1A8F4C);
  }
}
