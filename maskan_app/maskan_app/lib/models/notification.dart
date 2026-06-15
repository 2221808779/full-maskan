/// يمثل إشعاراً للتطبيق يُعرض للمستخدم
class AppNotification {
  /// المعرف الفريد للإشعار
  final int id;

  /// معرف المستخدم المستهدف
  final int userId;

  /// نوع الإشعار: booking / payment / maintenance / general
  final String? type;

  /// عنوان الإشعار
  final String title;

  /// محتوى الإشعار
  final String content;

  /// هل تمت قراءة الإشعار؟
  final bool isRead;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  AppNotification({
    required this.id,
    required this.userId,
    this.type,
    required this.title,
    required this.content,
    this.isRead = false,
    this.createdAt,
    this.updatedAt,
  });

  /// إنشاء إشعار من JSON ورد من API
  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      type: json['type'],
      title: json['title'] ?? '',
      content: json['content'] ?? json['body'] ?? json['message'] ?? '',
      isRead: json['read_at'] != null || json['is_read'] == true,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
