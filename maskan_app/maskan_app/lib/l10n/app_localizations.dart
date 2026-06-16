import 'package:flutter/material.dart';

/// ترجمة التطبيق — يدعم العربية (ar) والإنجليزية (en)
class AppLocalizations {
  /// اللغة الحالية للتطبيق
  final Locale locale;
  /// إنشاء كائن ترجمة للغة locale
  AppLocalizations(this.locale);

  /// هل اللغة الحالية هي العربية؟
  bool get isArabic => locale.languageCode == 'ar';

  /// الحصول على مثيل الترجمة من السياق
  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// اختيار النص حسب اللغة الحالية — النص العربي [ar] أو الإنجليزي [en]
  String tr(String ar, String en) => isArabic ? ar : en;

  // --- General ---
  String get appName => tr('مسكن', 'Maskan');
  String get tagline => tr('إدارة عقاراتك بذكاء', 'Manage your property smartly');
  String get loading => tr('جاري التحميل...', 'Loading...');
  String get close => tr('إغلاق', 'Close');
  String get cancel => tr('إلغاء', 'Cancel');
  String get confirm => tr('تأكيد', 'Confirm');
  String get back => tr('رجوع', 'Back');
  String get save => tr('حفظ', 'Save');
  String get delete => tr('حذف', 'Delete');
  String get search => tr('بحث', 'Search');
  String get noData => tr('لا توجد بيانات', 'No data');
  String get errorOccurred => tr('حدث خطأ، حاول مرة أخرى', 'An error occurred, try again');
  String get all => tr('الكل', 'All');

  // --- Auth ---
  String get login => tr('تسجيل الدخول', 'Login');
  String get register => tr('إنشاء حساب', 'Create Account');
  String get registerNewAccount => tr('إنشاء الحساب', 'Create Account');
  String get phoneNumber => tr('رقم الهاتف', 'Phone Number');
  String get password => tr('كلمة المرور', 'Password');
  String get confirmPassword => tr('تأكيد كلمة المرور', 'Confirm Password');
  String get fullName => tr('الاسم الكامل', 'Full Name');
  String get forgotPassword => tr('نسيت كلمة المرور؟', 'Forgot Password?');
  String get or => tr('أو', 'Or');
  String get enterAsGuest => tr('الدخول كزائر', 'Enter as Guest');
  String get noAccount => tr('ليس لديك حساب؟ ', "Don't have an account? ");
  String get haveAccount => tr('لديك حساب بالفعل؟ ', 'Already have an account? ');
  String get createAccount => tr('إنشاء حساب', 'Create Account');
  String get loginRequired => tr('تسجيل الدخول مطلوب', 'Login Required');
  String get featureRequiresLogin => tr('هذه الميزة تتطلب تسجيل الدخول',
      'This feature requires login');
  String get loginRequiredFor => tr('يجب تسجيل الدخول أولاً لـ ', 'Login required for ');
  String get platformTitle => tr('منصة إدارة العقارات', 'Property Management Platform');
  String get welcomeBack => tr('مرحباً بعودتك', 'Welcome Back');

  // --- Register ---
  String get chooseAccountType => tr('اختر نوع الحساب', 'Choose Account Type');
  String get tenant => tr('مستأجر', 'Tenant');
  String get tenantDesc => tr('أبحث عن عقار', 'Looking for a property');
  String get owner => tr('مالك عقار', 'Property Owner');
  String get ownerDesc => tr('أؤجر عقاراتي', 'Rent my properties');
  String get technician => tr('فني', 'Technician');
  String get technicianDesc => tr('أقدّم خدمات صيانة', 'Maintenance services');
  String get weak => tr('ضعيفة', 'Weak');
  String get medium => tr('متوسطة', 'Medium');
  String get good => tr('جيدة', 'Good');
  String get strong => tr('قوية', 'Strong');
  String get passwordStrength => tr('قوة كلمة المرور: ', 'Password strength: ');
  String get agreeTerms => tr('أوافق على ', 'I agree to the ');
  String get termsAndConditions => tr('الشروط والأحكام', 'Terms & Conditions');
  String get pleaseAgreeTerms => tr('يرجى الموافقة على الشروط والأحكام',
      'Please agree to the terms and conditions');
  String get enterName => tr('أدخل الاسم', 'Enter your name');
  String get enterPhone => tr('أدخل رقم الهاتف', 'Enter phone number');
  String get phoneMustBe10Digits => tr('رقم الهاتف يجب أن يكون 10 أرقام', 'Phone number must be 10 digits');
  String get phoneInvalidLibyan => tr('رقم الهاتف يجب أن يبدأ 091 أو 092 أو 093 أو 094 أو 098', 'Phone must start with 091, 092, 093, 094, or 098');
  String get passwordTooShort => tr('كلمة المرور قصيرة جداً', 'Password is too short');
  String get passwordMismatch => tr('كلمة المرور غير متطابقة', 'Passwords do not match');
  String get yearsOfExperience => tr('سنوات الخبرة', 'Years of experience');
  String get years => tr('سنوات', 'Years');

  // --- Settings ---
  String get settings => tr('الإعدادات', 'Settings');
  String get appearance => tr('المظهر', 'Appearance');
  String get darkMode => tr('الوضع الداكن', 'Dark Mode');
  String get enabled => tr('مفعل', 'Enabled');
  String get disabled => tr('غير مفعل', 'Disabled');
  String get language => tr('اللغة', 'Language');
  String get arabic => tr('العربية', 'Arabic');
  String get english => tr('English', 'English');
  String get aboutSection => tr('حول', 'About');
  String get appVersion => tr('إصدار التطبيق', 'App Version');
  String get contactUs => tr('تواصل معنا', 'Contact Us');
  String get rightsReserved => tr('مسكن © 2026 — جميع الحقوق محفوظة',
      'Maskan © 2026 — All rights reserved');

  // --- Profile ---
  String get profile => tr('الملف الشخصي', 'Profile');
  String get editProfile => tr('تعديل الملف الشخصي', 'Edit Profile');
  String get myBookings => tr('حجوزاتي', 'My Bookings');
  String get myFavorites => tr('المفضلة', 'Favorites');
  String get maintenance => tr('الصيانة', 'Maintenance');
  String get complaints => tr('الشكاوى', 'Complaints');
  String get accountSection => tr('الحساب', 'Account');
  String get supportSection => tr('الدعم', 'Support');
  String get advancedSection => tr('الحساب المتقدم', 'Advanced Account');
  String get notifications => tr('الإشعارات', 'Notifications');
  String get contactSupport => tr('تواصل مع الدعم', 'Contact Support');
  String get deactivateAccount => tr('إيقاف الحساب مؤقتاً', 'Deactivate Account');
  String get deleteAccount => tr('حذف الحساب', 'Delete Account');
  String get logout => tr('تسجيل الخروج', 'Logout');
  String get deactivateDesc => tr(
      'سيتم إيقاف حسابك مؤقتاً مع الاحتفاظ ببياناتك. هل تريد المتابعة؟',
      'Your account will be deactivated. Your data will be kept. Continue?');
  String get deleteAccountDesc => tr(
      'سيتم حذف حسابك نهائياً. هذا الإجراء لا يمكن التراجع عنه!',
      'Your account will be permanently deleted. This cannot be undone!');
  String get logoutConfirm => tr('هل أنت متأكد من تسجيل الخروج؟',
      'Are you sure you want to logout?');
  String get confirmDelete => tr('تأكيد الحذف', 'Confirm Delete');

  // --- Home ---
  String get home => tr('رئيسية', 'Home');
  String get bookings => tr('حجوزاتي', 'Bookings');
  String get account => tr('حسابي', 'Profile');
  String get searchHint => tr('ابحث عن عقار...', 'Search for a property...');
  String get welcome => tr('مرحباً', 'Welcome');
  String get findProperty => tr('ابحث عن عقارك المثالي اليوم',
      'Find your perfect property today');
  String get suggestedProperties => tr('عقارات مقترحة', 'Suggested Properties');
  String get viewAll => tr('عرض الكل', 'View All');
  String get featuredProperties => tr('عقارات مميزة', 'Featured Properties');
  String get noBookings => tr('لا توجد حجوزات', 'No bookings yet');
  String get browseProperties => tr('تصفح العقارات', 'Browse Properties');
  String get locationPermissionTitle => tr('الموقع الجغرافي', 'Location Access');
  String get locationPermissionDesc => tr('هل تسمح للتطبيق بالوصول إلى موقعك لعرض العقارات القريبة منك؟', 'Allow the app to access your location to show nearby properties?');
  String get allow => tr('سماح', 'Allow');
  String get deny => tr('رفض', 'Deny');
  String get nearbyProperties => tr('عقارات قريبة منك', 'Nearby Properties');
  String get allPropertiesLabel => tr('جميع العقارات', 'All Properties');
  String get kilometers => tr('كم', 'km');

  // --- Property Detail ---
  String get propertyNotFound => tr('العقار غير موجود', 'Property not found');
  String get description => tr('الوصف', 'Description');
  String get amenities => tr('المميزات', 'Amenities');
  String get ownerLabel => tr('المالك', 'Owner');
  String get reviews => tr('التقييمات', 'Reviews');
  String get locationMap => tr('الموقع على الخريطة', 'Location on Map');
  String get showMore => tr('عرض المزيد', 'Show more');
  String get showLess => tr('عرض أقل', 'Show less');
  String get noReviews => tr('لا توجد تقييمات بعد', 'No reviews yet');
  String get viewAllReviews => tr('عرض كل التقييمات', 'View all reviews');
  String get bookNow => tr('احجز الآن', 'Book Now');
  String get signupToBook => tr('سجّل لإتمام الحجز', 'Sign up to book');
  String get message => tr('مراسلة', 'Message');
  String get favorite => tr('إضافة للمفضلة', 'Add to favorites');
  String get realEstateAgent => tr('وسيط عقاري', 'Real Estate Agent');
  String get rooms => tr('غرف', 'Rooms');
  String get bathrooms => tr('حمام', 'Bathrooms');
  // Property types
  String get apartment => tr('شقة', 'Apartment');
  String get villa => tr('فيلا', 'Villa');
  String get studio => tr('استوديو', 'Studio');
  String get shop => tr('متجر', 'Shop');
  String get office => tr('مكتب', 'Office');
  String get warehouse => tr('مستودع', 'Warehouse');
  String get land => tr('أرض', 'Land');
  String get property => tr('عقار', 'Property');
  String get perMonth => tr('د.ل / شهر', 'LYD / month');
  // Amenities
  String get wifi => tr('واي فاي', 'WiFi');
  String get ac => tr('تكييف', 'A/C');
  String get parking => tr('موقف سيارات', 'Parking');
  String get security => tr('أمن', 'Security');
  String get laundry => tr('غسيل ملابس', 'Laundry');
  // Stats
  String get myStatsBookings => tr('حجوزاتي', 'My Bookings');
  String get myStatsReviews => tr('تقييماتي', 'My Reviews');
  String get myStatsFavorites => tr('المفضلة', 'Favorites');

  // --- OTP ---
  String get verifyOtp => tr('التحقق', 'Verify');
  String get otpTitle => tr('أدخل رمز التحقق', 'Enter verification code');
  String get otpSent => tr('تم إرسال رمز التحقق إلى ', 'Code sent to ');
  String get resendCode => tr('إعادة إرسال', 'Resend code');
  String get verify => tr('تحقق', 'Verify');
  String get accountCreated => tr('تم إنشاء الحساب', 'Account created');
  String get verificationCode => tr('رمز التحقق', 'Verification code');
  String get confirmCode => tr('تأكيد الرمز', 'Confirm code');

  // --- Forgot Password ---
  String get forgotPasswordTitle => tr('نسيت كلمة المرور', 'Forgot Password');
  String get enterPhoneToReset => tr('أدخل رقم هاتفك لإعادة تعيين كلمة المرور',
      'Enter your phone to reset password');
  String get sendCode => tr('إرسال الرمز', 'Send Code');
  String get resetPassword => tr('إعادة تعيين كلمة المرور', 'Reset Password');
  String get newPassword => tr('كلمة المرور الجديدة', 'New Password');
  String get reset => tr('إعادة تعيين', 'Reset');

  // --- Maintenance ---
  String get maintenanceRequests => tr('طلبات الصيانة', 'Maintenance Requests');
  String get newRequest => tr('طلب جديد', 'New Request');
  String get requestTitle => tr('عنوان الطلب', 'Request Title');
  String get requestDesc => tr('وصف المشكلة', 'Problem Description');
  String get submit => tr('إرسال', 'Submit');
  String get pending => tr('قيد الانتظار', 'Pending');
  String get inProgress => tr('قيد التنفيذ', 'In Progress');
  String get completed => tr('مكتمل', 'Completed');
  String get cancelled => tr('ملغي', 'Cancelled');

  // --- Notifications ---
  String get notificationsTitle => tr('الإشعارات', 'Notifications');
  String get markAllRead => tr('تحديد الكل كمقروء', 'Mark all as read');
  String get noNotifications => tr('لا توجد إشعارات', 'No notifications');

  // --- Chat ---
  String get conversations => tr('المحادثات', 'Conversations');
  String get chat => tr('محادثة', 'Chat');
  String get typeMessage => tr('اكتب رسالة...', 'Type a message...');
  String get send => tr('إرسال', 'Send');
  String get editMessage => tr('تعديل الرسالة', 'Edit Message');
  String get deleteMessage => tr('حذف الرسالة', 'Delete Message');
  String get deleteMessageTitle => tr('حذف الرسالة', 'Delete Message');
  String get deleteMessageConfirm => tr('هل أنت متأكد من حذف هذه الرسالة؟', 'Are you sure you want to delete this message?');
  String get editingMessage => tr('تعديل الرسالة...', 'Editing message...');
  String get deleteConversationTitle => tr('حذف المحادثة', 'Delete Conversation');
  String get deleteConversationConfirm => tr('هل أنت متأكد من حذف المحادثة مع', 'Are you sure you want to delete conversation with');

  // --- Payment ---
  String get payment => tr('الدفع', 'Payment');
  String get paymentMethod => tr('طريقة الدفع', 'Payment Method');
  String get totalAmount => tr('المبلغ الإجمالي', 'Total Amount');
  String get payNow => tr('ادفع الآن', 'Pay Now');
  String get paymentSuccess => tr('تم الدفع بنجاح', 'Payment Successful');
  String get paymentFailed => tr('فشل الدفع', 'Payment Failed');

  // --- Review Form ---
  String get addReview => tr('إضافة تقييم', 'Add Review');
  String get yourRating => tr('تقييمك', 'Your Rating');
  String get yourComment => tr('تعليقك', 'Your Comment');
  String get submitReview => tr('إرسال التقييم', 'Submit Review');
  String get myReview => tr('تقييمي', 'My Review');
  String get anonymous => tr('مجهول', 'Anonymous');

  // --- Booking Form ---
  String get newBooking => tr('حجز جديد', 'New Booking');
  String get startDate => tr('تاريخ البداية', 'Start Date');
  String get endDate => tr('تاريخ النهاية', 'End Date');

  // --- Onboarding ---
  String get skip => tr('تخطي', 'Skip');
  String get next => tr('التالي', 'Next');
  String get onboardingTitle1 => tr('استأجر بثقة ومرونة', 'Rent with confidence & flexibility');
  String get onboardingBody1 => tr('تصفّح مئات العقارات في مدينتك مع خرائط تفاعلية وفلاتر دقيقة',
      'Browse hundreds of properties with interactive maps & precise filters');
  String get onboardingTitle2 => tr('حجز سهل ودفع آمن', 'Easy booking & secure payment');
  String get onboardingBody2 => tr('احجز وادفع إلكترونياً عبر بوابة آمنة — كل شيء في تطبيق واحد',
      'Book & pay online via a secure gateway — everything in one app');
  String get onboardingTitle3 => tr('صيانة فورية بلمسة واحدة', 'Instant maintenance at one tap');
  String get onboardingBody3 => tr('أبلغ عن أعطال عقارك واتابع الصيانة لحظة بلحظة',
      'Report property issues & track maintenance in real time');

  // --- OTP / Verification ---
  String get verifyPhoneTitle => tr('التحقق من رقم هاتفك', 'Verify your phone number');
  String get otpSentTo => tr('أرسلنا رمز مكوّن من 6 أرقام إلى', 'We sent a 6-digit code to');
  String get changeNumber => tr('تغيير الرقم', 'Change number');
  String get invalidOtp => tr('رمز غير صحيح', 'Invalid code');
  String get otpResent => tr('تم إعادة إرسال رمز التحقق', 'Verification code resent');
  String get resendIn => tr('إعادة الإرسال خلال', 'Resend in');
  String get otpSentToYourNumber => tr('رمز التحقق المُرسل لرقمك', 'Verification code sent to your number');

  // --- Auth / Password ---
  String get countryCode => tr('+218', '+218');
  String get failedToSendCode => tr('فشل إرسال الرمز', 'Failed to send code');
  String get updatePassword => tr('تحديث كلمة المرور', 'Update Password');
  String get passwordChangedSuccess => tr('تم تغيير كلمة المرور بنجاح ✓', 'Password changed successfully ✓');
  String get resetFailed => tr('فشل إعادة التعيين', 'Reset failed');

  // --- Terms ---
  String get agree => tr('موافقة', 'Agree');
  String get introduction => tr('مقدمة', 'Introduction');
  String get pleaseReadAllTerms => tr('الرجاء قراءة جميع الشروط', 'Please read all terms');
  String get termsIntroText => tr(
      'مرحباً بك في تطبيق "مسكن". باستخدامك لهذا التطبيق، فإنك توافق على الالتزام بالشروط والأحكام التالية. يرجى قراءتها بعناية قبل استخدام الخدمات.',
      'Welcome to "Maskan". By using this app, you agree to the following terms. Please read carefully.');
  String get termsSection1Title => tr('1. تعريفات', '1. Definitions');
  String get termsSection1P1 => tr(
      'التطبيق: تطبيق "مسكن" لإدارة العقارات وخدمات الصيانة.',
      'App: "Maskan" for property management & maintenance.');
  String get termsSection1P2 => tr(
      'المستخدم: أي شخص يستخدم التطبيق، سواء كان زائراً أو مستأجراً أو فنياً.',
      'User: any person using the app (visitor, tenant, or technician).');
  String get termsSection1P3 => tr(
      'الخدمات: خدمات إدارة العقارات، الحجز، الصيانة، والتواصل بين الأطراف.',
      'Services: property management, booking, maintenance & communication.');
  String get termsSection2Title => tr('2. التسجيل والحساب', '2. Registration & Account');
  String get termsSection2P1 => tr(
      'يجب على المستخدم تقديم معلومات دقيقة وكاملة عند إنشاء الحساب.',
      'User must provide accurate info when creating an account.');
  String get termsSection2P2 => tr(
      'المستخدم مسؤول عن الحفاظ على سرية معلومات الدخول الخاصة به.',
      'User is responsible for keeping login credentials confidential.');
  String get termsSection2P3 => tr(
      'يحق للتطبيق تعليق أو إلغاء أي حساب في حال انتهاك الشروط.',
      'App may suspend or cancel accounts violating terms.');
  String get termsSection2P4 => tr(
      'لا يُسمح بإنشاء أكثر من حساب لنفس الشخص.',
      'Multiple accounts per person are not allowed.');
  String get termsSection3Title => tr('3. استخدام الخدمات', '3. Using Services');
  String get termsSection3P1 => tr(
      'خدمة الحجز: تتيح للمستأجرين حجز العقارات المتاحة وفقاً للتواريخ المحددة.',
      'Booking: allows tenants to book available properties.');
  String get termsSection3P2 => tr(
      'خدمة الصيانة: تتيح تقديم طلبات صيانة وتعيين الفنيين المختصين.',
      'Maintenance: allows submitting requests & assigning technicians.');
  String get termsSection3P3 => tr(
      'خدمة التواصل: تتيح التواصل بين المستأجرين ومالكي العقارات والفنيين.',
      'Communication: enables contact between tenants, owners & technicians.');
  String get termsSection3P4 => tr(
      'يجب استخدام الخدمات بطريقة قانونية وأخلاقية.',
      'Services must be used legally and ethically.');
  String get termsSection4Title => tr('4. المدفوعات', '4. Payments');
  String get termsSection4P1 => tr(
      'يتم تحديد أسعار الحجز والعقارات من قبل مالكي العقارات.',
      'Booking prices are set by property owners.');
  String get termsSection4P2 => tr(
      'جميع المدفوعات تتم وفقاً للأسعار المعلنة في التطبيق.',
      'All payments follow the prices shown in the app.');
  String get termsSection4P3 => tr(
      'سياسة الاسترداد تخضع لشروط الحجز المتفق عليها.',
      'Refund policy is subject to booking terms.');
  String get termsSection5Title => tr('5. المسؤولية', '5. Liability');
  String get termsSection5P1 => tr(
      'التطبيق وسيط بين المستأجرين ومالكي العقارات والفنيين.',
      'App is a mediator between tenants, owners & technicians.');
  String get termsSection5P2 => tr(
      'التطبيق غير مسؤول عن أي نزاعات تنشأ بين المستخدمين.',
      'App is not liable for disputes between users.');
  String get termsSection5P3 => tr(
      'المستخدم مسؤول عن دقة المعلومات التي يقدمها.',
      'User is responsible for info accuracy.');
  String get termsSection5P4 => tr(
      'التطبيق يبذل قصارى جهده لضمان جودة الخدمات.',
      'App strives to ensure service quality.');
  String get termsSection6Title => tr('6. الخصوصية', '6. Privacy');
  String get termsSection6P1 => tr(
      'يتم جمع المعلومات الشخصية لغرض تحسين الخدمات.',
      'Personal info is collected to improve services.');
  String get termsSection6P2 => tr(
      'لن يتم مشاركة المعلومات الشخصية مع أطراف ثالثة دون موافقة المستخدم.',
      'Personal info won\'t be shared with third parties without consent.');
  String get termsSection6P3 => tr(
      'للمستخدم الحق في طلب حذف بياناته في أي وقت.',
      'User can request data deletion at any time.');
  String get termsSection6P4 => tr(
      'نستخدم إجراءات أمنية لحماية بيانات المستخدمين.',
      'We use security measures to protect user data.');
  String get termsSection7Title => tr('7. التعديلات', '7. Modifications');
  String get termsSection7P1 => tr(
      'يحتفظ التطبيق بالحق في تعديل هذه الشروط في أي وقت.',
      'App reserves the right to modify terms at any time.');
  String get termsSection7P2 => tr(
      'سيتم إخطار المستخدمين بالتعديلات الجوهرية.',
      'Users will be notified of material changes.');
  String get termsSection7P3 => tr(
      'استمرار استخدام التطبيق بعد التعديلات يعني الموافقة عليها.',
      'Continued use after changes means acceptance.');
  String get termsSection8Title => tr('8. أحكام عامة', '8. General Provisions');
  String get termsSection8P1 => tr(
      'تخضع هذه الشروط للقوانين الليبية.',
      'These terms are governed by Libyan law.');
  String get termsSection8P2 => tr(
      'في حال وجود أي استفسار، يرجى التواصل معنا عبر صفحة الإعدادات.',
      'For inquiries, contact us via settings page.');
  String get termsSection8P3 => tr(
      'هذه الشروط سارية المفعول اعتباراً من تاريخ استخدام التطبيق.',
      'These terms are effective from first app use.');

  // --- Home & Search ---
  String get filter => tr('فلتر', 'Filter');
  String get allProperties => tr('جميع العقارات', 'All Properties');
  String get loginToBookMore => tr('سجّل الدخول للحجز والمزيد من المميزات',
      'Login to book & more features');
  String get noPropertiesAvailable => tr('لا توجد عقارات متاحة', 'No properties available');
  String get tryChangingFilters => tr('جرّب تغيير الفلاتر', 'Try changing filters');
  String get retry => tr('إعادة المحاولة', 'Retry');
  String get sort => tr('ترتيب', 'Sort');

  // --- Bookings ---
  String get bookYourFirstProperty => tr('قم بحجز عقارك الأول الآن', 'Book your first property now');
  String get confirmCancelBooking => tr('هل أنت متأكد من إلغاء هذا الحجز؟',
      'Are you sure you want to cancel this booking?');
  String get currencyLyd => tr('د.ل', 'LYD');
  String get amount => tr('المبلغ:', 'Amount:');

  // --- Booking Form / Calendar ---
  String get selectBookingDates => tr('اختر تواريخ الحجز', 'Select booking dates');
  String get selectStartEndDates => tr('اختر تاريخ البداية والنهاية', 'Select start & end dates');
  String get numberOfGuests => tr('عدد الضيوف', 'Number of guests');
  String get guests => tr('ضيوف', 'Guests');
  String get chooseDates => tr('اختر التواريخ', 'Choose dates');
  String get confirmBooking => tr('تأكيد الحجز', 'Confirm Booking');
  String get blockedDatesInRange => tr('يوجد تواريخ محجوزة في هذا النطاق',
      'Some dates are blocked in this range');
  String get pleaseSelectDate => tr('يرجى اختيار تاريخ الحجز', 'Please select a booking date');
  String get bookingRequestSent => tr('تم إرسال طلب الحجز بنجاح', 'Booking request sent successfully');
  String get createBookingFailed => tr('فشل إنشاء الحجز', 'Failed to create booking');
  String get month1 => tr('يناير', 'Jan');
  String get month2 => tr('فبراير', 'Feb');
  String get month3 => tr('مارس', 'Mar');
  String get month4 => tr('أبريل', 'Apr');
  String get month5 => tr('مايو', 'May');
  String get month6 => tr('يونيو', 'Jun');
  String get month7 => tr('يوليو', 'Jul');
  String get month8 => tr('أغسطس', 'Aug');
  String get month9 => tr('سبتمبر', 'Sep');
  String get month10 => tr('أكتوبر', 'Oct');
  String get month11 => tr('نوفمبر', 'Nov');
  String get month12 => tr('ديسمبر', 'Dec');
  String get dayAbbr1 => tr('ح', 'Sat');
  String get dayAbbr2 => tr('ن', 'Sun');
  String get dayAbbr3 => tr('ث', 'Mon');
  String get dayAbbr4 => tr('ر', 'Tue');
  String get dayAbbr5 => tr('خ', 'Wed');
  String get dayAbbr6 => tr('ج', 'Thu');
  String get dayAbbr7 => tr('س', 'Fri');
  String get from => tr('من:', 'From:');
  String get to => tr('إلى:', 'To:');
  String get nights => tr('ليال', 'Nights');
  String get available => tr('متاح', 'Available');
  String get bookedBlocked => tr('محجوز', 'Blocked');
  String get selected => tr('محدد', 'Selected');

  // --- Payment ---
  String get bookingDetails => tr('تفاصيل الحجز', 'Booking Details');
  String get numberOfNights => tr('عدد الليالي', 'Number of nights');
  String get plutuOnlinePayment => tr('Plutu للدفع الإلكتروني', 'Plutu Online Payment');
  String get creditCardOrEWallet => tr('بطاقة ائتمانية أو محفظة إلكترونية',
      'Credit card or e-wallet');
  String get cashOnDelivery => tr('الدفع عند الاستلام', 'Cash on Delivery');
  String get cashOnHandover => tr('سداد نقدي عند تسلم العقار', 'Cash on property handover');
  String get plutuRedirectInfo => tr(
      'سيتم تحويلك إلى بوابة Plutu للدفع الإلكتروني الآمن',
      'You will be redirected to Plutu secure payment gateway');
  String get proceedToPlutu => tr('متابعة إلى Plutu', 'Proceed to Plutu');
  String get paymentGatewayFailed => tr('فشل الاتصال ببوابة الدفع', 'Payment gateway connection failed');
  String get bookingCashOnDelivery => tr('تم تسجيل الحجز - الدفع عند الاستلام',
      'Booking registered - Cash on delivery');
  String get paymentProcessingFailed => tr('فشل معالجة الدفع: ', 'Payment processing failed: ');
  String get checkingPaymentStatus => tr('جاري التحقق من حالة الدفع...',
      'Checking payment status...');
  String get thankYouBookingConfirmed => tr('شكراً لك، تم تأكيد حجزك بنجاح',
      'Thank you, your booking is confirmed');
  String get paymentCancelled => tr('تم إلغاء عملية الدفع، يمكنك المحاولة لاحقاً',
      'Payment cancelled, you can try again later');
  String get paymentErrorContactSupport => tr(
      'حدث خطأ أثناء معالجة الدفع، يرجى التواصل مع الدعم',
      'Payment error, please contact support');
  String get backToHome => tr('العودة للرئيسية', 'Back to Home');
  String get backToBookings => tr('العودة للحجوزات', 'Back to Bookings');

  // --- Favorites ---
  String get noFavoriteProperties => tr('لا توجد عقارات مفضلة', 'No favorite properties');
  String get addFavoritesToFollow => tr('أضف عقارات إلى المفضلة لمتابعتها',
      'Add properties to favorites to follow them');

  // --- Maintenance ---
  String get noMaintenanceRequests => tr('لا توجد طلبات صيانة', 'No maintenance requests');
  String get sendRequestForActiveBooking => tr(
      'يمكنك إرسال طلب صيانة لأي حجز نشط',
      'Send a maintenance request for any active booking');
  String get electricity => tr('كهرباء', 'Electricity');
  String get plumbing => tr('سباكة', 'Plumbing');
  String get acMaintenance => tr('تكييف', 'A/C');
  String get painting => tr('دهانات', 'Painting');
  String get carpentry => tr('نجارة', 'Carpentry');
  String get other => tr('أخرى', 'Other');
  String get technicianLabel => tr('الفني: ', 'Technician: ');
  String get maintenanceType => tr('نوع الصيانة', 'Maintenance Type');
  String get describeProblemHint => tr('اشرح المشكلة بالتفصيل...', 'Describe the problem in detail...');
  String get descriptionRequired => tr('الوصف مطلوب', 'Description required');
  String get willNotifyRequestStatus => tr(
      'سيتم إشعارك بحالة الطلب فور معالجته',
      'You will be notified when your request is processed');
  String get maintenanceRequestSent => tr('تم إرسال طلب الصيانة بنجاح',
      'Maintenance request sent successfully');
  String get maintenanceRequestFailed => tr('فشل إرسال الطلب', 'Failed to send request');

  // --- Reviews ---
  String get propertyTarget => tr('العقار', 'Property');
  String get ownerTarget => tr('المالك', 'Owner');
  String get technicianTarget => tr('الفني', 'Technician');
  String get rate => tr('قيم ', 'Rate ');
  String get shareYourExperience => tr('شارك تجربتك مع المستخدمين الآخرين',
      'Share your experience with other users');
  String get bad => tr('سيء', 'Bad');
  String get excellent => tr('ممتاز', 'Excellent');
  String get writeReviewHint => tr('اكتب رأيك...', 'Write your review...');
  String get pleaseSelectRating => tr('يرجى اختيار التقييم', 'Please select a rating');
  String get reviewSubmitted => tr('تم إرسال التقييم بنجاح', 'Review submitted successfully');
  String get reviewSubmitFailed => tr('فشل إرسال التقييم', 'Failed to submit review');

  // --- Complaints ---
  String get newComplaint => tr('شكوى جديدة', 'New Complaint');
  String get complaintTitle => tr('عنوان الشكوى', 'Complaint Title');
  String get complaintSummaryHint => tr('ملخص مختصر للشكوى', 'Brief summary of complaint');
  String get complaintTitleRequired => tr('العنوان مطلوب', 'Title is required');
  String get complaintDetails => tr('تفاصيل الشكوى', 'Complaint Details');
  String get complaintDetailHint => tr('اشرح المشكلة بالتفصيل...', 'Describe the problem in detail...');
  String get complaintDetailsRequired => tr('التفاصيل مطلوبة', 'Details are required');
  String get submitComplaint => tr('إرسال الشكوى', 'Submit Complaint');
  String get complaintSent => tr('تم إرسال الشكوى بنجاح', 'Complaint sent successfully');
  String get complaintFailed => tr('فشل إرسال الشكوى', 'Failed to send complaint');

  // --- Technician ---
  String get tasks => tr('المهام', 'Tasks');
  String get myTasks => tr('مهامي', 'My Tasks');
  String get availableTasks => tr('مهام متاحة', 'Available Tasks');
  String get maintenanceTasks => tr('مهام الصيانة', 'Maintenance Tasks');
  String get manageAssignedTasks => tr('إدارة طلبات الصيانة المسندة إليك',
      'Manage maintenance requests assigned to you');
  String get newStatus => tr('جديد', 'New');
  String get noTasksCurrently => tr('لا توجد مهام حالياً', 'No tasks currently');
  String get tasksWillAppearHere => tr('عندما يتم تعيين مهام لك ستظهر هنا',
      'Tasks assigned to you will appear here');
  String get noAvailableTasks => tr('لا توجد مهام متاحة حالياً', 'No available tasks');
  String get checkBackLater => tr('تحقق لاحقاً لوجود مهام جديدة', 'Check back later for new tasks');
  String get generalMaintenance => tr('صيانة عامة', 'General Maintenance');
  String get inProgressTag => tr('جاري العمل', 'In Progress');
  String get taskDetails => tr('تفاصيل المهمة', 'Task Details');
  String get taskNotFound => tr('المهمة غير موجودة', 'Task not found');
  String get requestInfo => tr('معلومات الطلب', 'Request Info');
  String get requestId => tr('رقم الطلب', 'Request ID');
  String get createdDate => tr('تاريخ الإنشاء', 'Created Date');
  String get lastUpdate => tr('آخر تحديث', 'Last Update');
  String get closureNotes => tr('ملاحظات الإغلاق', 'Closure Notes');
  String get notes => tr('ملاحظات', 'Notes');
  String get addNotesHint => tr('أضف ملاحظاتك...', 'Add your notes...');
  String get acceptTask => tr('قبول المهمة', 'Accept Task');
  String get accept => tr('قبول', 'Accept');
  String get requestAccepted => tr('تم قبول الطلب', 'Request accepted');
  String get reject => tr('رفض', 'Reject');
  String get rejectTask => tr('رفض المهمة', 'Reject Task');
  String get reasonOptional => tr('السبب (اختياري)', 'Reason (optional)');
  String get startExecution => tr('بدء التنفيذ', 'Start Execution');
  String get closeTaskCompleted => tr('إغلاق الطلب - مكتمل', 'Close Task - Completed');
  String get taskCompleted => tr('تم إكمال هذا الطلب', 'This task is completed');
  String get taskAccepted => tr('تم قبول المهمة', 'Task accepted');
  String get taskAcceptFailed => tr('فشل قبول المهمة', 'Failed to accept task');
  String get taskRejected => tr('تم رفض المهمة', 'Task rejected');
  String get rejectFailed => tr('فشل الرفض', 'Reject failed');
  String get executionStarted => tr('تم بدء التنفيذ', 'Execution started');
  String get statusUpdated => tr('تم تحديث الحالة', 'Status updated');
  String get statusUpdateFailed => tr('فشل تحديث الحالة', 'Status update failed');
  String get pleaseEnterClosureNotes => tr('يرجى إدخال ملاحظات الإغلاق',
      'Please enter closure notes');
  String get taskClosed => tr('تم إغلاق الطلب', 'Task closed');
  String get taskCloseFailed => tr('فشل إغلاق الطلب', 'Failed to close task');

  String get noResultsFound => tr('لم يتم العثور على نتائج', 'No results found');

  // --- Edit Profile ---
  String get changePhoto => tr('تغيير الصورة', 'Change Photo');
  String get camera => tr('الكاميرا', 'Camera');
  String get gallery => tr('معرض الصور', 'Gallery');
  String get deletePhoto => tr('حذف الصورة', 'Delete Photo');
  String get profileUpdated => tr('تم تحديث ملفك الشخصي', 'Profile updated');
  String get profileUpdateFailed => tr('فشل تحديث البيانات', 'Failed to update profile');
  String get changePhotoSheetTitle => tr('تغيير الصورة', 'Change Photo');
  String get photoUpdated => tr('تم تحديث الصورة', 'Photo updated');
  String get photoUpdateFailed => tr('فشل تحديث الصورة', 'Failed to update photo');
  String get photoDeleted => tr('تم حذف الصورة', 'Photo deleted');
  String get photoDeleteFailed => tr('فشل حذف الصورة', 'Failed to delete photo');

  // --- Chat / Conversations ---
  String get noConversationsYet => tr('لا توجد محادثات بعد', 'No conversations yet');
  String get userFallback => tr('مستخدم', 'User');
  String get noMessagesYet => tr('لا توجد رسائل بعد', 'No messages yet');

}

/// مفوض الترجمة — يُستخدم من Flutter لتحميل ملف الترجمة حسب اللغة
class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  /// هل اللغة [locale] مدعومة (ar أو en)؟
  @override
  bool isSupported(Locale locale) => ['ar', 'en'].contains(locale.languageCode);

  /// تحميل ملف الترجمة للغة [locale]
  @override
  Future<AppLocalizations> load(Locale locale) async {
    return AppLocalizations(locale);
  }

  /// هل يجب إعادة تحميل الترجمة عند تغيير اللغة؟ (لا)
  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}
