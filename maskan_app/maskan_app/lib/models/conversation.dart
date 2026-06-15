/// يمثل محادثة بين مستخدمين (مراسلة مباشرة)
class Conversation {
  /// المعرف الفريد للمحادثة
  final int id;

  /// معرف المستخدم الأول (صاحب المحادثة)
  final int userOneId;

  /// معرف المستخدم الثاني (الطرف الآخر)
  final int userTwoId;

  /// معرف العقار المرتبط (إن وجد)
  final int? propertyId;

  /// معرف طلب الصيانة المرتبط (إن وجد)
  final int? maintenanceRequestId;

  /// تاريخ آخر رسالة
  final String? lastMessageAt;

  /// اسم المستخدم الآخر (للشاشات)
  final String? otherUserName;

  /// رابط صورة المستخدم الآخر
  final String? otherUserAvatar;

  /// نوع المستخدم الآخر: tenant / owner / technician
  final String? otherUserType;

  /// نص آخر رسالة
  final String? lastMessage;

  /// عدد الرسائل غير المقروءة
  final int unreadCount;

  Conversation({
    required this.id,
    required this.userOneId,
    required this.userTwoId,
    this.propertyId,
    this.maintenanceRequestId,
    this.lastMessageAt,
    this.otherUserName,
    this.otherUserAvatar,
    this.otherUserType,
    this.lastMessage,
    this.unreadCount = 0,
  });

  /// إنشاء محادثة من JSON ورد من API
  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      id: json['id'] ?? 0,
      userOneId: json['user_one_id'] ?? 0,
      userTwoId: json['user_two_id'] ?? 0,
      propertyId: json['property_id'],
      maintenanceRequestId: json['maintenance_request_id'],
      lastMessageAt: json['last_message_at'],
      otherUserName: json['other_user_name'],
      otherUserAvatar: json['other_user_avatar'],
      otherUserType: json['other_user_type'],
      lastMessage: json['last_message'],
      unreadCount: json['unread_count'] ?? 0,
    );
  }
}
