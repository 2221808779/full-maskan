/// مسارات (نقاط نهاية) API للتطبيق — جميع مسارات الخادم الخلفي (Laravel)
///
/// مقسمة حسب الوظيفة: المصادقة، العقارات، الحجوزات، المدفوعات، الصيانة،
/// الإشعارات، المفضلة، التقييمات، المحادثات، والإدارة.
/// جميع المسارات تُسبق بـ `/api` عند الاستخدام الفعلي.
class ApiEndpoints {
  // ── المصادقة — تسجيل الدخول / إنشاء حساب / استعادة كلمة المرور (بدون توكن) ──
  static const String register = 'auth/register';
  static const String login = 'auth/login';
  static const String sendOtp = 'auth/send-otp';
  static const String verifyOtp = 'auth/verify-otp';
  static const String resetPassword = 'auth/reset-password';
  static const String specialties = 'specialties';

  // ── المصادقة — الملف الشخصي / تسجيل الخروج (بعد تسجيل الدخول) ──
  static const String logout = 'auth/logout';
  static const String profile = 'auth/profile';
  static const String updateProfile = 'auth/profile';

  // ── العقارات — تصفح / بحث (عام) ──
  static const String properties = 'properties';
  static String propertyDetail(int id) => 'properties/$id';

  // ── العقارات — إدارة المالك (إضافة / تعديل / حذف) ──
  static const String createProperty = 'properties';
  static const String uploadPhoto = 'auth/profile/photo';
  static const String deactivateAccount = 'auth/deactivate';
  static const String deleteAccount = 'auth/delete';

  // ── الحجوزات — إنشاء / عرض / إلغاء / تأكيد الحجوزات ──
  static const String bookings = 'bookings';
  static const String createBooking = 'bookings';
  static String cancelBooking(int id) => 'bookings/$id/cancel';
  /// تواريخ الحجز المحجوزة (Blackout dates) — لعرضها في التقويم
  static String propertyBlackoutDates(int id) => 'properties/$id/blackout-dates';

  // ── المدفوعات — دفع نقدي / إلكتروني عبر Plutu ──
  /// بدء عملية دفع عبر Plutu — توجيه المستخدم لبوابة الدفع
  static const String plutuInitiate = 'plutu/initiate';
  /// التحقق من حالة دفع Plutu
  static String plutuCheck(int bookingId) => 'plutu/check/$bookingId';

  // ── طلبات الصيانة — تقديم / تعيين فني / تحديث الحالة ──
  static const String maintenanceRequests = 'maintenance-requests';
  static const String createMaintenance = 'maintenance-requests';
  static String assignMaintenance(int id) => 'maintenance-requests/$id/assign';
  static String rejectMaintenance(int id) => 'maintenance-requests/$id/reject';
  static String updateMaintenanceStatus(int id) => 'maintenance-requests/$id/status';
  static String closeMaintenance(int id) => 'maintenance-requests/$id/status';
  static String claimMaintenance(int id) => 'technician/maintenance-requests/$id/claim';
  static const String pendingMaintenance = 'technician/maintenance-requests/pending';

  // ── الإشعارات — عرض / تحديد كمقروء ──
  static const String notifications = 'notifications';
  static const String unreadCount = 'notifications/unread-count';
  static String markNotificationRead(int id) => 'notifications/$id/read';
  static const String markAllRead = 'notifications/read-all';

  // ── المفضلة — إضافة / إزالة / التحقق من المفضلة ──
  static const String favorites = 'favorites';
  static const String toggleFavorite = 'favorites/toggle';
  static const String checkFavorite = 'favorites/check';

  // ── التقييمات — إضافة / تعديل / حذف تقييمات العقارات ──
  static const String reviews = 'reviews';
  static const String createReview = 'reviews';
  static String propertyReviews(int id) => 'properties/$id/reviews';

  // ── المحادثات — الدردشة بين المستأجر والمالك ──
  static const String conversations = 'conversations';
  static String conversationMessages(int id) => 'conversations/$id/messages';
  static String deleteConversation(int id) => 'conversations/$id';
  static const String messages = 'messages';
  static String editMessage(int id) => 'messages/$id';
  static String deleteMessage(int id) => 'messages/$id';
  static String markAsRead(int userId) => 'messages/$userId/read';

  // ── الشكاوى — تقديم / عرض الشكاوى ──
  static const String complaints = 'complaints';

}
