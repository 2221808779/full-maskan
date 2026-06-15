/// يمثل عقاراً — الوحدة الأساسية في التطبيق (شقة، فيلا، إلخ)
class Property {
  /// المعرف الفريد للعقار
  final int id;

  /// معرف المالك
  final int ownerId;

  /// عنوان العقار
  final String title;

  /// نوع العقار: apartment / villa / office / studio
  final String propertyType;

  /// وصف العقار التفصيلي
  final String description;

  /// الموقع (نص وصفي)
  final String location;

  /// السعر (شهري/سنوي)
  final double price;

  /// عدد الغرف
  final int roomsCount;

  /// عدد دورات المياه
  final int bathroomsCount;

  /// حالة العقار: available / rented / maintenance
  final String status;

  /// قائمة روابط صور العقار
  final List<String> images;

  /// خط العرض (للموقع الجغرافي)
  final double? latitude;

  /// خط الطول (للموقع الجغرافي)
  final double? longitude;

  /// المساحة بالمتر المربع
  final double? area;

  /// هل يوجد مسبح؟
  final bool hasPool;

  /// هل يوجد موقف سيارة؟
  final bool hasParking;

  /// هل يوجد مكيف؟
  final bool hasAc;

  /// هل العقار مفروش؟
  final bool hasFurniture;

  /// متوسط التقييم (0-5)
  final double avgRating;

  /// عدد التقييمات
  final int reviewCount;

  /// اسم المالك (للعرض السريع)
  final String? ownerName;

  /// رقم هاتف المالك
  final String? ownerPhone;

  /// رابط صورة المالك
  final String? ownerAvatar;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  Property({
    required this.id,
    required this.ownerId,
    required this.title,
    required this.propertyType,
    required this.description,
    required this.location,
    required this.price,
    required this.roomsCount,
    required this.bathroomsCount,
    required this.status,
    required this.images,
    this.latitude,
    this.longitude,
    this.area,
    this.hasPool = false,
    this.hasParking = false,
    this.hasAc = false,
    this.hasFurniture = false,
    this.avgRating = 0.0,
    this.reviewCount = 0,
    this.ownerName,
    this.ownerPhone,
    this.ownerAvatar,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء عقار من JSON ورد من API
  factory Property.fromJson(Map<String, dynamic> json) {
    return Property(
      id: json['id'] ?? 0,
      ownerId: json['owner_id'] ?? 0,
      title: json['title'] ?? '',
      propertyType: json['property_type'] ?? json['type'] ?? '',
      description: json['description'] ?? '',
      location: json['location'] ?? '',
      price: json['price'] is num
          ? (json['price'] as num).toDouble()
          : double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      roomsCount: json['rooms_count'] ?? json['bedrooms'] ?? json['rooms'] ?? 0,
      bathroomsCount: json['bathrooms_count'] ?? json['bathrooms'] ?? 0,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      area: (json['area'] as num?)?.toDouble(),
      hasPool: json['has_pool'] == 1 || json['has_pool'] == true,
      hasParking: json['has_parking'] == 1 || json['has_parking'] == true,
      hasAc: json['has_ac'] == 1 || json['has_ac'] == true,
      hasFurniture: json['has_furniture'] == 1 || json['has_furniture'] == true,
      status: json['status'] ?? 'available',
      images: json['images'] != null
          ? (json['images'] as List<dynamic>).map<String>((img) {
              if (img is String) return img;
              if (img is Map) return (img['image_path'] ?? img['url'] ?? '') as String;
              return '';
            }).where((s) => s.isNotEmpty).toList()
          : [],
      avgRating: json['avg_rating'] is num
          ? (json['avg_rating'] as num).toDouble()
          : double.tryParse(json['avg_rating']?.toString() ?? '') ??
              (json['rating'] is num
                  ? (json['rating'] as num).toDouble()
                  : double.tryParse(json['rating']?.toString() ?? '0') ?? 0.0),
      reviewCount: json['review_count'] ?? 0,
      ownerName: json['owner'] != null
          ? (json['owner'] as Map)['full_name'] ?? (json['owner'] as Map)['name']
          : json['owner_name'],
      ownerPhone: json['owner'] != null
          ? (json['owner'] as Map)['phone']
          : json['owner_phone'],
      ownerAvatar: json['owner'] != null
          ? (json['owner'] as Map)['profile_image'] ?? (json['owner'] as Map)['avatar']
          : json['owner_avatar'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  /// تنسيق السعر للعرض (1K, 1.5M)
  String get priceFormatted {
    if (price >= 1000000) {
      return '${(price / 1000000).toStringAsFixed(1)}M';
    } else if (price >= 1000) {
      return '${(price / 1000).toStringAsFixed(0)}K';
    }
    return price.toStringAsFixed(0);
  }
}
