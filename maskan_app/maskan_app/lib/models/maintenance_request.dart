/// يمثل طلب صيانة — إبلاغ عن مشكلة في عقار وتعيين فني
class MaintenanceRequest {
  /// المعرف الفريد لطلب الصيانة
  final int id;

  /// معرف العقار المرتبط
  final int? propertyId;

  /// معرف المستأجر الذي أرسل الطلب
  final int? tenantId;

  /// معرف الفني المسند إليه الطلب
  final int? technicianId;

  /// وصف المشكلة
  final String problemDescription;

  /// تصنيف المشكلة بواسطة الذكاء الاصطناعي
  final String? aiCategory;

  /// دقة تصنيف الذكاء الاصطناعي (0.0 - 1.0)
  final double? aiAccuracy;

  /// حالة الطلب: pending / assigned / in_progress / completed
  final String status;

  /// اسم الفني المسند
  final String? technicianName;

  /// رقم هاتف الفني
  final String? technicianPhone;

  /// اسم العقار (للعرض السريع)
  final String? propertyTitle;

  /// اسم المستأجر (للعرض في مهام الفني)
  final String? tenantName;

  /// تاريخ التعيين
  final String? assignedAt;

  /// تاريخ الإكمال
  final String? completedAt;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  MaintenanceRequest({
    required this.id,
    this.propertyId,
    this.tenantId,
    this.technicianId,
    required this.problemDescription,
    this.aiCategory,
    this.aiAccuracy,
    required this.status,
    this.technicianName,
    this.technicianPhone,
    this.propertyTitle,
    this.tenantName,
    this.assignedAt,
    this.completedAt,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء طلب صيانة من JSON ورد من API
  factory MaintenanceRequest.fromJson(Map<String, dynamic> json) {
    return MaintenanceRequest(
      id: json['id'] ?? 0,
      propertyId: json['property_id'],
      tenantId: json['tenant_id'],
      technicianId: json['technician_id'],
      problemDescription: json['problem_description'] ?? json['description'] ?? '',
      aiCategory: json['ai_category'] ?? json['type'] ?? json['category'],
      aiAccuracy: (json['ai_accuracy'] as num?)?.toDouble(),
      status: json['status'] ?? 'pending',
      technicianName: json['technician_name'],
      technicianPhone: json['technician_phone'],
      propertyTitle: json['property_title'],
      tenantName: json['tenant_name'],
      assignedAt: json['assigned_at'],
      completedAt: json['completed_at'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  /// تسمية حالة الطلب بالعربية
  String get statusLabel {
    switch (status) {
      case 'pending': return 'قيد الانتظار';
      case 'assigned': return 'تم التعيين';
      case 'in_progress': return 'قيد التنفيذ';
      case 'completed': return 'مكتمل';
      default: return status;
    }
  }
}
