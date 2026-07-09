import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';

/// شاشة البداية (Splash) التي تظهر عند إطلاق التطبيق
/// تقوم بتشغيل حركة Fade-in و Scale على أيقونة التطبيق ثم تنتقل إلى
/// الشاشة المناسبة حسب حالة تسجيل الدخول
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

/// حالة [SplashScreen] التي تدير الحركة ومنطق التنقل
class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _fadeIn;
  late final Animation<double> _scale;

  /// يهيئ وحدات تحكم الحركة ويبدأ حركة شاشة البداية. بعد تأخير، ينتقل إلى الشاشة التالية.
  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    );
    _fadeIn = CurvedAnimation(parent: _controller, curve: Curves.easeOut);
    _scale = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutBack),
    );
    _controller.forward();
    Future.delayed(const Duration(milliseconds: 2200), _navigate);
  }

  /// يتخلص من وحدة تحكم الحركة.
  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  /// يتحقق من حالة المصادقة وينتقل إلى الشاشة الرئيسية أو شاشة التعريف.
  Future<void> _navigate() async {
    if (!mounted) return;
    final auth = context.read<AuthProvider>();
    if (auth.isLoggedIn) {
      if (auth.isTechnician) {
        context.go(AppRoutes.technicianHome);
      } else {
        context.go(AppRoutes.tenantHome);
      }
    } else {
      context.go(AppRoutes.onboarding);
    }
  }

  /// يبني شاشة البداية مع حركة التلاشي والتكبير على أيقونة التطبيق.
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      height: double.infinity,
      color: Colors.white,
      child: Center(
        child: FadeTransition(
          opacity: _fadeIn,
          child: ScaleTransition(
            scale: _scale,
            child: Image.asset(
              'assets/images/app_icon.png',
              width: 180,
              height: 180,
              fit: BoxFit.contain,
            ),
          ),
        ),
      ),
    );
  }
}
