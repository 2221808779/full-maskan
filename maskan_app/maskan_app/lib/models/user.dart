/// نموذج المستخدم — يمثل بيانات المستخدم في التطبيق
///
/// الأدوار المتاحة: [tenant] مستأجر، [owner] مالك عقار، [technician] فني، [admin] مشرف
///
/// خصائص الحالة: [status] يمكن أن يكون 'active'، 'suspended'، أو 'banned'
/// مع [banReason] و [bannedUntil] لتحديد تفاصيل الحظر
class User {
  /// المعرف الفريد للمستخدم
  final int id;

  /// الاسم الكامل
  final String fullName;

  /// رقم الهاتف — يستخدم لتسجيل الدخول
  final String? phone;

  /// رابط صورة الملف الشخصي
  final String? profileImage;

  /// تاريخ الميلاد
  final DateTime? birthDate;

  /// تاريخ توثيق رقم الهاتف
  final String? phoneVerifiedAt;

  /// رمز FCM للإشعارات الفورية (Firebase)
  final String? fcmToken;

  /// نوع المستخدم: tenant / owner / technician / admin / visitor
  final String userType;

  /// حالة الحساب: active / suspended / banned
  final String status;

  /// سبب الحظر
  final String? banReason;

  /// تاريخ الحظر
  final String? bannedAt;

  /// تاريخ انتهاء الحظر
  final String? bannedUntil;

  /// الدور (مرونة إضافية)
  final String? role;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  User({
    required this.id,
    required this.fullName,
    this.phone,
    this.profileImage,
    this.birthDate,
    this.phoneVerifiedAt,
    this.fcmToken,
    required this.userType,
    this.status = 'active',
    this.banReason,
    this.bannedAt,
    this.bannedUntil,
    this.role,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء مستخدم من JSON ورد من API
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      fullName: json['full_name'] ?? json['name'] ?? '',
      phone: json['phone'] ?? '',
      profileImage: json['profile_image'] ?? json['avatar'],
      birthDate: json['birth_date'] != null ? DateTime.tryParse(json['birth_date']) : null,
      phoneVerifiedAt: json['phone_verified_at'],
      fcmToken: json['fcm_token'],
      userType: json['user_type'] ?? json['role'] ?? 'visitor',
      status: json['status'] ?? 'active',
      banReason: json['ban_reason'],
      bannedAt: json['banned_at'],
      bannedUntil: json['banned_until'],
      role: json['role'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  /// تحويل المستخدم إلى JSON لتخزينه محلياً
  Map<String, dynamic> toJson() => {
    'full_name': fullName,
    'phone': phone,
    'profile_image': profileImage,
    'banned_until': bannedUntil,
    'user_type': userType,
  };

  /// هل المستخدم مستأجر؟
  bool get isTenant => userType == 'tenant';

  /// هل المستخدم فني صيانة؟
  bool get isTechnician => userType == 'technician';

  /// هل المستخدم مالك عقار؟
  bool get isOwner => userType == 'owner';

  /// هل المستخدم مشرف (Admin)؟
  bool get isAdmin => userType == 'admin';

  User copyWith({
    int? id,
    String? fullName,
    String? phone,
    String? profileImage,
    DateTime? birthDate,
    String? phoneVerifiedAt,
    String? fcmToken,
    String? userType,
    String? status,
    String? banReason,
    String? bannedAt,
    String? bannedUntil,
    String? role,
    String? createdAt,
    String? updatedAt,
  }) => User(
    id: id ?? this.id,
    fullName: fullName ?? this.fullName,
    phone: phone ?? this.phone,
    profileImage: profileImage ?? this.profileImage,
    birthDate: birthDate ?? this.birthDate,
    phoneVerifiedAt: phoneVerifiedAt ?? this.phoneVerifiedAt,
    fcmToken: fcmToken ?? this.fcmToken,
    userType: userType ?? this.userType,
    status: status ?? this.status,
    banReason: banReason ?? this.banReason,
    bannedAt: bannedAt ?? this.bannedAt,
    bannedUntil: bannedUntil ?? this.bannedUntil,
    role: role ?? this.role,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
  );
}
