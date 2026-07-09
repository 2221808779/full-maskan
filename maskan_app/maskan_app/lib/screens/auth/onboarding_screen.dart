import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import '../../l10n/app_localizations.dart';
import '../../config/routes.dart';

/// شاشة تعريفية للمستخدمين الجدد مع صفحات متعددة تشرح ميزات التطبيق وأزرار دعوة للإجراء
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

/// حالة [OnboardingScreen] التي تدير التنقل بين الصفحات ومؤشرات النقاط
class _OnboardingScreenState extends State<OnboardingScreen> {
  final _controller = PageController();
  int _current = 0;

  /// ما إذا كانت الصفحة المعروضة حالياً هي الأخيرة.
  bool get isLast => _current == 2;

  /// يتخلص من وحدة التحكم في الصفحات.
  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  /// يبني شاشة التعريف مع خلفية متدرجة، عرض الصفحات، مؤشرات النقاط، وأزرار التنقل.
  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context)!;
    final pages = [
      _PageData(
        title: loc.onboardingTitle1,
        body: loc.onboardingBody1,
        imageUrl:
            'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?w=600&q=90',
      ),
      _PageData(
        title: loc.onboardingTitle2,
        body: loc.onboardingBody2,
        imageUrl:
            'https://images.unsplash.com/photo-1618220179428-22790b461013?w=600&q=90',
      ),
      _PageData(
        title: loc.onboardingTitle3,
        body: loc.onboardingBody3,
        imageUrl:
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=90',
      ),
    ];

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
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
                child: Row(
                  children: [
                    TextButton(
                      onPressed: () => context.go(AppRoutes.visitorHome),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
                        foregroundColor: Colors.white70,
                      ),
                      child: Text(
                        loc.skip,
                        style: const TextStyle(
                          fontFamily: 'Cairo',
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const Spacer(),
                    _buildDots(pages.length),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              Expanded(
                child: PageView.builder(
                  controller: _controller,
                  onPageChanged: (i) => setState(() => _current = i),
                  itemCount: pages.length,
                  itemBuilder: (_, i) => _buildPage(pages[i]),
                ),
              ),
              const SizedBox(height: 12),
              _buildBottom(loc),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  /// يبني صفحة تعريفية واحدة مع صورة وعنوان ونص.
  Widget _buildPage(_PageData page) {
    final screenHeight = MediaQuery.of(context).size.height;
    final isShort = screenHeight < 700;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        children: [
          Expanded(
            flex: isShort ? 3 : 4,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.network(
                page.imageUrl,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => Container(
                  color: const Color(0xFF1a3a5c),
                  child: Center(
                    child: SvgPicture.asset(
                      'assets/images/house-icon.svg',
                      width: 60,
                      height: 60,
                      colorFilter: const ColorFilter.mode(
                        Color(0xFFC49A2B),
                        BlendMode.srcIn,
                      ),
                    ),
                  ),
                ),
                loadingBuilder: (_, child, progress) {
                  if (progress == null) return child;
                  return Container(
                    color: const Color(0xFF1a3a5c),
                    child: Center(
                      child: CircularProgressIndicator(
                        value: progress.expectedTotalBytes != null
                            ? progress.cumulativeBytesLoaded /
                                  progress.expectedTotalBytes!
                            : null,
                        color: const Color(0xFFC49A2B),
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: EdgeInsets.all(isShort ? 16 : 20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x4D000000),
                  blurRadius: 40,
                  offset: Offset(0, 12),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'مسكن',
                  style: TextStyle(
                    fontSize: isShort ? 17 : 19,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1A1D22),
                    fontFamily: 'Cairo',
                  ),
                ),
                SizedBox(height: isShort ? 2 : 4),
                Text(
                  page.title,
                  style: TextStyle(
                    fontSize: isShort ? 15 : 17,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF1A1D22),
                    fontFamily: 'Cairo',
                  ),
                  textAlign: TextAlign.center,
                ),
                SizedBox(height: isShort ? 6 : 8),
                Text(
                  page.body,
                  style: TextStyle(
                    fontSize: isShort ? 12 : 13,
                    color: const Color(0xFF989EA7),
                    fontFamily: 'Cairo',
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// يبني شريط الإجراءات السفلي بأزرار "التالي" أو "تسجيل الدخول" أو "إنشاء حساب" حسب الصفحة الحالية.
  Widget _buildBottom(AppLocalizations loc) {
    final screenHeight = MediaQuery.of(context).size.height;
    final isShort = screenHeight < 700;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: isLast
          ? Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: SizedBox(
                        height: isShort ? 46 : 54,
                        child: ElevatedButton(
                          onPressed: () => context.go(AppRoutes.login),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF1a3a5c),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            elevation: 0,
                            textStyle: TextStyle(
                              fontSize: isShort ? 14 : 16,
                              fontWeight: FontWeight.w700,
                              fontFamily: 'Cairo',
                            ),
                          ),
                          child: FittedBox(
                            fit: BoxFit.scaleDown,
                            child: Text(loc.login, textAlign: TextAlign.center),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: SizedBox(
                        height: isShort ? 46 : 54,
                        child: OutlinedButton(
                          onPressed: () => context.go(AppRoutes.register),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(
                              color: Colors.white38,
                              width: 1.5,
                            ),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            textStyle: TextStyle(
                              fontSize: isShort ? 14 : 16,
                              fontWeight: FontWeight.w700,
                              fontFamily: 'Cairo',
                            ),
                          ),
                          child: FittedBox(
                            fit: BoxFit.scaleDown,
                            child: Text(loc.register, textAlign: TextAlign.center),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                Padding(
                  padding: EdgeInsets.only(top: isShort ? 8 : 12),
                  child: TextButton(
                    onPressed: () => context.go(AppRoutes.visitorHome),
                    child: Text(
                      loc.browseProperties,
                      style: const TextStyle(
                        fontFamily: 'Cairo',
                        fontSize: 13,
                        color: Colors.white54,
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ),
                ),
              ],
            )
          : SizedBox(
              width: double.infinity,
              height: isShort ? 42 : 48,
              child: ElevatedButton(
                onPressed: () => _controller.nextPage(
                  duration: const Duration(milliseconds: 400),
                  curve: Curves.easeInOut,
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1a3a5c),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  elevation: 0,
                  textStyle: TextStyle(
                    fontSize: isShort ? 14 : 16,
                    fontWeight: FontWeight.w700,
                    fontFamily: 'Cairo',
                  ),
                ),
                child: Text(loc.next),
              ),
            ),
    );
  }

  /// يبني صفاً من نقاط مؤشر الصفحات المتحركة.
  Widget _buildDots(int count) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(count, (i) {
        final active = i == _current;
        return AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          margin: const EdgeInsets.symmetric(horizontal: 3),
          width: active ? 20 : 6,
          height: 6,
          decoration: BoxDecoration(
            color: active ? const Color(0xFFC49A2B) : Colors.white30,
            borderRadius: BorderRadius.circular(3),
          ),
        );
      }),
    );
  }
}

/// بيانات صفحة تعريفية واحدة — العنوان والنص وصورة الخلفية
class _PageData {
  final String title;
  final String body;
  final String imageUrl;

  const _PageData({
    required this.title,
    required this.body,
    required this.imageUrl,
  });
}
