/// يمثل عقاراً مفضلاً — لتتبع العقارات التي أضافها المستخدم إلى المفضلة
class Favorite {
  /// المعرف الفريد
  final int id;

  /// معرف المستخدم الذي أضاف العقار
  final int userId;

  /// معرف العقار
  final int propertyId;

  /// اسم العقار (للعرض السريع)
  final String? propertyTitle;

  /// رابط صورة العقار
  final String? propertyImage;

  /// سعر العقار
  final double? propertyPrice;

  /// عنوان العقار
  final String? propertyAddress;

  /// تاريخ الإضافة إلى المفضلة
  final String? addedAt;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  Favorite({
    this.id = 0,
    this.userId = 0,
    required this.propertyId,
    this.propertyTitle,
    this.propertyImage,
    this.propertyPrice,
    this.propertyAddress,
    this.addedAt,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء مفضلة من JSON ورد من API
  factory Favorite.fromJson(Map<String, dynamic> json) {
    return Favorite(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      propertyId: json['property_id'] ?? 0,
      propertyTitle: json['property'] != null
          ? (json['property'] as Map)['title']
          : json['property_title'],
      propertyImage: json['property'] != null
          ? (((json['property'] as Map)['images'] as List?)?.isNotEmpty == true
              ? ((json['property'] as Map)['images'] as List).first is String
                  ? ((json['property'] as Map)['images'] as List).first as String
                  : (((json['property'] as Map)['images'] as List).first as Map)['image_path'] ?? ''
              : null)
          : json['property_image'],
      propertyPrice: json['property'] != null
          ? ((json['property'] as Map)['price'] as num?)?.toDouble()
          : json['property_price']?.toDouble(),
      propertyAddress: json['property'] != null
          ? (json['property'] as Map)['location']
          : json['property_address'],
      addedAt: json['added_at'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
