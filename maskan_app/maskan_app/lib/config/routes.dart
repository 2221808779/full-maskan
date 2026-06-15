import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../screens/auth/splash_screen.dart';
import '../screens/auth/onboarding_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/otp_verification_screen.dart';
import '../screens/auth/forgot_password_screen.dart';
import '../screens/auth/reset_password_screen.dart';
import '../screens/auth/terms_screen.dart';
import '../screens/visitor/home_screen.dart';
import '../screens/visitor/property_list_screen.dart';
import '../screens/visitor/property_detail_screen.dart';
import '../screens/tenant/tenant_home_screen.dart';
import '../screens/tenant/booking_list_screen.dart';
import '../screens/tenant/booking_detail_screen.dart';
import '../screens/tenant/booking_form_screen.dart';
import '../screens/tenant/payment_screen.dart';
import '../screens/tenant/payment_callback_screen.dart';
import '../screens/tenant/favorites_screen.dart';
import '../screens/tenant/maintenance_request_screen.dart';
import '../screens/tenant/maintenance_form_screen.dart';
import '../screens/tenant/review_form_screen.dart';
import '../screens/tenant/complaint_form_screen.dart';
import '../screens/technician/technician_home_screen.dart';
import '../screens/technician/task_detail_screen.dart';
import '../screens/technician/technician_reviews_screen.dart';
import '../screens/shared/profile_screen.dart';
import '../screens/shared/edit_profile_screen.dart';
import '../screens/shared/notifications_screen.dart';
import '../screens/shared/conversations_screen.dart';
import '../screens/shared/chat_screen.dart';
import '../screens/shared/settings_screen.dart';
import '../core/widgets/route_shell.dart';

/// مسارات (Routes) تطبيق مسكن — تعرّف جميع شاشات التطبيق وروابطها
///
/// تحتوي على:
/// - ثوابت المسارات (static const String) لكل شاشة
/// - قائمة المسارات العامة للزوار [visitorPublicPaths]
/// - إعدادات GoRouter الرئيسية [router] مع منطق إعادة التوجيه [_redirectLogic]
class AppRoutes {
  /// المسار الابتدائي — شاشة البداية (Splash)
  static const String splash = '/';
  /// شاشة الدليل التعريفي (Onboarding) — تظهر للمستخدم الجديد
  static const String onboarding = '/onboarding';
  /// شاشة تسجيل الدخول
  static const String login = '/login';
  /// شاشة إنشاء حساب جديد
  static const String register = '/register';
  /// شاشة التحقق من رمز OTP
  static const String otpVerification = '/otp-verification';
  /// شاشة نسيت كلمة المرور
  static const String forgotPassword = '/forgot-password';
  /// شاشة إعادة تعيين كلمة المرور
  static const String resetPassword = '/reset-password';
  /// الصفحة الرئيسية للزائر (غير مسجّل الدخول)
  static const String visitorHome = '/visitor-home';
  /// قائمة العقارات مع إمكانية البحث
  static const String propertyList = '/properties';
  /// تفاصيل عقار فردي — يحتوي على المعرف :id
  static const String propertyDetail = '/properties/:id';
  /// الصفحة الرئيسية للمستأجر (بعد تسجيل الدخول)
  static const String tenantHome = '/tenant-home';
  /// قائمة الحجوزات
  static const String bookings = '/bookings';
  /// تفاصيل الحجز — يحتوي على معرف الحجز :bookingId
  static const String bookingDetail = '/booking-detail/:bookingId';
  /// نموذج حجز عقار — يحتوي على معرف العقار :propertyId
  static const String bookingForm = '/booking-form/:propertyId';
  /// شاشة الدفع — تحتوي على معرف الحجز :bookingId
  static const String payment = '/payment/:bookingId';
  /// رد الاتصال بعد نجاح الدفع
  static const String paymentCallback = '/payment/callback';
  /// رد الاتصال بعد إلغاء الدفع
  static const String paymentCancel = '/payment/cancel';
  /// قائمة العقارات المفضلة
  static const String favorites = '/favorites';
  /// قائمة طلبات الصيانة
  static const String maintenanceRequests = '/maintenance-requests';
  /// نموذج طلب صيانة — يحتوي على معرف العقار :propertyId
  static const String maintenanceForm = '/maintenance-form/:propertyId';
  /// نموذج تقييم — يحتوي على نوع الهدف ومعرفه
  static const String reviewForm = '/review-form/:targetType/:targetId';
  /// نموذج تقديم شكوى
  static const String complaintForm = '/complaint-form';
  /// الصفحة الرئيسية للفني
  static const String technicianHome = '/technician-home';
  /// تفاصيل مهمّة فني — تحتوي على معرف المهمة :id
  static const String taskDetail = '/task/:id';
  /// قائمة تقييمات الفني
  static const String technicianReviews = '/technician-reviews';
  /// الملف الشخصي للمستخدم
  static const String profile = '/profile';
  /// تعديل الملف الشخصي
  static const String editProfile = '/edit-profile';
  /// قائمة الإشعارات
  static const String notifications = '/notifications';
  /// قائمة المحادثات
  static const String conversations = '/conversations';
  /// شاشة الدردشة — تحتوي على معرف المحادثة :conversationId
  static const String chat = '/chat/:conversationId';
  /// شاشة الشروط والأحكام
  static const String terms = '/terms';
  /// شاشة الإعدادات
  static const String settings = '/settings';

  /// المسارات العامة التي يمكن للزائر (غير مسجّل الدخول) الوصول إليها
  static final List<String> visitorPublicPaths = [
    splash, onboarding, login, register, otpVerification,
    forgotPassword, resetPassword, terms,
    visitorHome, propertyList, propertyDetail, login, register, onboarding,
  ];

  /// المسارات المخصصة للمستأجر فقط — يمنع الفني من الوصول إليها
  static final List<String> tenantOnlyPaths = [
    tenantHome, propertyList, propertyDetail,
    bookings, bookingDetail.replaceAll(':bookingId', ''),
    bookingForm.replaceAll(':propertyId', ''),
    payment.replaceAll(':bookingId', ''), paymentCallback, paymentCancel,
    favorites, reviewForm.replaceAll(':targetType', '').replaceAll(':targetId', ''),
    complaintForm,
  ];

  /// إعدادات GoRouter الرئيسية — تحتوي على جميع المسارات ومنطق إعادة التوجيه
  static final GoRouter router = GoRouter(
    initialLocation: splash,
    redirect: _redirectLogic,
    routes: [
      GoRoute(path: splash, builder: (_, _) => const SplashScreen()),
      GoRoute(path: onboarding, builder: (_, _) => const OnboardingScreen()),
      GoRoute(path: login, builder: (_, _) => const LoginScreen()),
      GoRoute(path: register, builder: (_, _) => const RegisterScreen()),
      GoRoute(
        path: otpVerification,
        builder: (_, state) => OtpVerificationScreen(
          phone: state.extra as String? ?? '',
        ),
      ),
      GoRoute(path: terms, builder: (_, _) => const TermsScreen()),
      GoRoute(path: forgotPassword, builder: (_, _) => const ForgotPasswordScreen()),
      GoRoute(
        path: resetPassword,
        builder: (_, state) => ResetPasswordScreen(
          phone: state.extra as String? ?? '',
        ),
      ),
      GoRoute(path: visitorHome, builder: (_, _) => const RouteShell(child: VisitorHomeScreen())),
      GoRoute(
        path: propertyList,
        builder: (_, state) => RouteShell(
          child: PropertyListScreen(searchQuery: state.extra as String?),
        ),
      ),
      GoRoute(
        path: propertyDetail,
        builder: (_, state) => RouteShell(
          child: PropertyDetailScreen(
            propertyId: int.parse(state.pathParameters['id']!),
          ),
        ),
      ),
      GoRoute(path: tenantHome, builder: (_, _) => const RouteShell(child: TenantHomeScreen())),
      GoRoute(path: bookings, builder: (_, _) => const RouteShell(child: BookingListScreen())),
      GoRoute(
        path: bookingDetail,
        builder: (_, state) {
          final bookingId = state.extra is int
              ? state.extra as int
              : int.parse(state.pathParameters['bookingId']!);
          return RouteShell(child: BookingDetailScreen(bookingId: bookingId));
        },
      ),
      GoRoute(
        path: bookingForm,
        builder: (_, state) => RouteShell(
          child: BookingFormScreen(
            propertyId: int.parse(state.pathParameters['propertyId']!),
          ),
        ),
      ),
      GoRoute(
        path: paymentCallback,
        builder: (_, _) => const PaymentCallbackScreen(type: 'callback'),
      ),
      GoRoute(
        path: paymentCancel,
        builder: (_, _) => const PaymentCallbackScreen(type: 'cancel'),
      ),
      GoRoute(
        path: payment,
        builder: (_, state) => RouteShell(
          child: PaymentScreen(
            bookingId: int.parse(state.pathParameters['bookingId']!),
          ),
        ),
      ),
      GoRoute(path: favorites, builder: (_, _) => const RouteShell(child: FavoritesScreen())),
      GoRoute(path: maintenanceRequests, builder: (_, _) => const RouteShell(child: MaintenanceRequestScreen())),
      GoRoute(
        path: maintenanceForm,
        builder: (_, state) => RouteShell(
          child: MaintenanceFormScreen(
            propertyId: int.parse(state.pathParameters['propertyId']!),
          ),
        ),
      ),
      GoRoute(
        path: reviewForm,
        builder: (_, state) {
          final extra = state.extra is Map ? state.extra as Map : <String, dynamic>{};
          return RouteShell(
            child: ReviewFormScreen(
              targetType: state.pathParameters['targetType']!,
              targetId: int.parse(state.pathParameters['targetId']!),
              propertyId: extra['propertyId'] as int?,
              bookingId: extra['bookingId'] as int?,
            ),
          );
        },
      ),
      GoRoute(path: complaintForm, builder: (_, _) => const RouteShell(child: ComplaintFormScreen())),
      GoRoute(path: technicianHome, builder: (_, _) => const RouteShell(child: TechnicianHomeScreen())),
      GoRoute(
        path: taskDetail,
        builder: (_, state) => RouteShell(
          child: TaskDetailScreen(
            taskId: int.parse(state.pathParameters['id']!),
          ),
        ),
      ),
      GoRoute(path: technicianReviews, builder: (_, _) => const RouteShell(child: TechnicianReviewsScreen())),
      GoRoute(path: profile, builder: (_, _) => const RouteShell(child: ProfileScreen())),
      GoRoute(path: editProfile, builder: (_, _) => const RouteShell(child: EditProfileScreen())),
      GoRoute(path: notifications, builder: (_, _) => const RouteShell(child: NotificationsScreen())),
      GoRoute(path: conversations, builder: (_, _) => const RouteShell(child: ConversationsScreen())),
      GoRoute(
        path: chat,
        builder: (_, state) => RouteShell(
          child: ChatScreen(
            conversationId: int.parse(state.pathParameters['conversationId']!),
          ),
        ),
      ),
      GoRoute(path: settings, builder: (_, _) => const RouteShell(child: SettingsScreen())),
    ],
  );

  /// منطق إعادة التوجيه — يتحقق من حالة تسجيل الدخول ويعيد التوجيه حسب
  /// صلاحيات المستخدم (زائر / مستأجر / فني)
  ///
  /// إذا لم يكن المستخدم مسجّلاً وكان المسار غير عام، يُعاد توجيهه إلى [login].
  /// إذا كان مسجّلاً وكان المسار من مسارات الزوار، يُعاد توجيهه حسب نوع المستخدم.
  /// Checks if [path] starts with any of the given [prefixes].
  static bool _pathStartsWith(String path, List<String> prefixes) {
    for (final p in prefixes) {
      if (path.startsWith(p)) return true;
    }
    return false;
  }

  static String? _redirectLogic(BuildContext context, GoRouterState state) {
    final auth = context.read<AuthProvider>();
    final isLoggedIn = auth.isLoggedIn;
    final path = state.matchedLocation;

    if (!isLoggedIn && !visitorPublicPaths.contains(path)) {
      return login;
    }

    if (isLoggedIn) {
      if (path == splash || path == login || path == register ||
          path == onboarding || path == forgotPassword || path == visitorHome) {
        if (auth.isTechnician) return technicianHome;
        return tenantHome;
      }

      // منع الفني من الوصول إلى مسارات المستأجر
      if (auth.isTechnician && _pathStartsWith(path, tenantOnlyPaths)) {
        return technicianHome;
      }
    }

    return null;
  }
}
