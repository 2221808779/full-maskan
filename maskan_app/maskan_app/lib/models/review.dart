/// يمثل تقييماً — تقييم عقار أو فني من قبل مستخدم
class Review {
  /// المعرف الفريد للتقييم
  final int id;

  /// معرف المستخدم الذي كتب التقييم
  final int userId;

  /// معرف العقار المُقيَّم
  final int propertyId;

  /// معرف الفني المُقيَّم (إن وجد)
  final int? technicianId;

  /// عدد النجوم (1-5)
  final int stars;

  /// نص التعليق
  final String? comment;

  /// اسم المُقيِّم (للعرض)
  final String? reviewerName;

  /// رابط صورة المُقيِّم
  final String? reviewerAvatar;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  Review({
    required this.id,
    required this.userId,
    required this.propertyId,
    this.technicianId,
    required this.stars,
    this.comment,
    this.reviewerName,
    this.reviewerAvatar,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء تقييم من JSON ورد من API
  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      propertyId: json['property_id'] ?? json['target_id'] ?? 0,
      technicianId: json['technician_id'],
      stars: json['stars'] ?? json['rating'] ?? 0,
      comment: json['comment'],
      reviewerName: json['user'] != null
          ? (json['user'] as Map)['full_name'] ?? (json['user'] as Map)['name']
          : json['reviewer_name'],
      reviewerAvatar: json['user'] != null
          ? (json['user'] as Map)['profile_image'] ?? (json['user'] as Map)['avatar']
          : json['reviewer_avatar'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
