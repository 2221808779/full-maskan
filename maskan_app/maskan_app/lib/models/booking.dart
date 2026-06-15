/// نموذج الحجز — يمثل حجز عقار من قبل المستأجر
///
/// يحتوي على تفاصيل الحجز: العقار، التواريخ، السعر، الحالة، وطريقة الدفع.
/// حالات الحجز: [pending] قيد الانتظار، [confirmed] مؤكد، [active] نشط،
/// [completed] مكتمل، [cancelled] ملغي.
class Booking {
  /// المعرف الفريد للحجز
  final int id;

  /// معرف المستخدم (المستأجر)
  final int userId;

  /// معرف العقار المحجوز
  final int propertyId;

  /// اسم العقار (للعرض السريع)
  final String? propertyTitle;

  /// رابط صورة العقار
  final String? propertyImage;

  /// عنوان العقار
  final String? propertyAddress;

  /// تاريخ بداية الحجز
  final DateTime startDate;

  /// تاريخ نهاية الحجز
  final DateTime endDate;

  /// السعر الإجمالي
  final double totalPrice;

  /// حالة الحجز: pending / confirmed / active / completed / cancelled
  final String status;

  /// طريقة الدفع: cash / plutu
  final String paymentMethod;

  /// حالة الدفع: pending / paid / refunded
  final String paymentStatus;

  /// ملاحظات إضافية من المستأجر
  final String? notes;

  /// تاريخ الأرشفة (للحجوزات القديمة)
  final String? archivedAt;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  Booking({
    required this.id,
    required this.userId,
    required this.propertyId,
    this.propertyTitle,
    this.propertyImage,
    this.propertyAddress,
    required this.startDate,
    required this.endDate,
    required this.totalPrice,
    required this.status,
    this.paymentMethod = 'cash',
    this.paymentStatus = 'pending',
    this.notes,
    this.archivedAt,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء حجز من JSON ورد من API
  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      propertyId: json['property_id'] ?? 0,
      propertyTitle: json['property'] != null
          ? (json['property'] as Map)['title']
          : json['property_title'],
      propertyImage: json['property'] != null
          ? ((json['property'] as Map)['images'] as List?)?.isNotEmpty == true
              ? ((json['property'] as Map)['images'] as List).first is String
                  ? ((json['property'] as Map)['images'] as List).first as String
                  : (((json['property'] as Map)['images'] as List).first as Map)['image_path'] ?? ''
              : null
          : json['property_image'],
      propertyAddress: json['property'] != null
          ? (json['property'] as Map)['location']
          : json['property_address'],
      startDate: DateTime.parse(json['start_date'] ?? json['created_at']),
      endDate: DateTime.parse(json['end_date'] ?? json['created_at']),
      totalPrice: json['total_price'] is num ? (json['total_price'] as num).toDouble() : double.tryParse(json['total_price']?.toString() ?? '0') ?? 0.0,
      status: json['status'] ?? 'pending',
      paymentMethod: json['payment_method'] ?? 'cash',
      paymentStatus: json['payment_status'] ?? 'pending',
      notes: json['notes'],
      archivedAt: json['archived_at'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  /// تسمية الحالة بالعربية
  String get statusLabel {
    switch (status) {
      case 'pending': return 'قيد الانتظار';
      case 'confirmed': return 'مؤكد';
      case 'active': return 'نشط';
      case 'completed': return 'مكتمل';
      case 'cancelled': return 'ملغي';
      default: return status;
    }
  }

  /// هل الحجز نشط (مؤكد أو نشط)؟
  bool get isActive => status == 'active' || status == 'confirmed';

  /// هل يمكن إلغاء الحجز؟
  bool get canCancel => status == 'pending' || status == 'confirmed';

  /// عدد الليالي = الفرق بين تاريخ البداية والنهاية
  int get nights => endDate.difference(startDate).inDays;

  /// نسخ الحجز مع إمكانية تغيير المعرف
  Booking copyWith({int? id}) => Booking(
    id: id ?? this.id,
    userId: userId,
    propertyId: propertyId,
    propertyTitle: propertyTitle,
    propertyImage: propertyImage,
    propertyAddress: propertyAddress,
    startDate: startDate,
    endDate: endDate,
    totalPrice: totalPrice,
    status: status,
    paymentMethod: paymentMethod,
    paymentStatus: paymentStatus,
    notes: notes,
    archivedAt: archivedAt,
    createdAt: createdAt,
    updatedAt: updatedAt,
  );
}
