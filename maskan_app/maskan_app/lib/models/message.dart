/// يمثل رسالة داخل محادثة
class Message {
  /// المعرف الفريد للرسالة
  final int id;

  /// معرف المحادثة
  final int conversationId;

  /// معرف المرسل
  final int senderId;

  /// معرف المستقبل
  final int receiverId;

  /// نص الرسالة
  final String messageText;

  /// حالة الرسالة: sent / delivered / read
  final String status;

  /// نوع الرسالة: text / image / complaint
  final String? messageType;

  /// حالة الشكوى: pending / reviewed / resolved
  final String? complaintStatus;

  /// رد المشرف على الشكوى
  final String? adminResponse;

  /// تاريخ التعديل
  final String? editedAt;

  /// قائمة معرفات المستخدمين الذين حذفوا الرسالة
  final List<int>? deletedFor;

  /// تاريخ الإرسال
  final String? sentAt;

  /// معرف من قام بالرد (للمشرفين)
  final int? respondedBy;

  /// تاريخ حل الشكوى
  final String? resolvedAt;

  /// تاريخ الإنشاء
  final String? createdAt;

  /// تاريخ آخر تحديث
  final String? updatedAt;

  /// هل تم تعديل الرسالة؟
  final bool isEdited;

  /// هل تمت قراءة الرسالة؟
  final bool isRead;

  Message({
    required this.id,
    required this.conversationId,
    required this.senderId,
    required this.receiverId,
    required this.messageText,
    this.status = 'sent',
    this.messageType,
    this.complaintStatus,
    this.adminResponse,
    this.editedAt,
    this.deletedFor,
    this.sentAt,
    this.respondedBy,
    this.resolvedAt,
    this.createdAt,
    this.updatedAt,
    this.isEdited = false,
    this.isRead = false,
  });

  /// إنشاء رسالة من JSON ورد من API
  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'] ?? 0,
      conversationId: json['conversation_id'] ?? 0,
      senderId: json['sender_id'] ?? 0,
      receiverId: json['receiver_id'] ?? 0,
      messageText: json['message_text'] ?? json['content'] ?? json['message'] ?? '',
      status: json['status'] ?? 'sent',
      messageType: json['type'],
      complaintStatus: json['complaint_status'],
      adminResponse: json['admin_response'],
      editedAt: json['edited_at'],
      deletedFor: json['deleted_for'] != null
          ? List<int>.from(json['deleted_for'].map((e) => e is int ? e : int.tryParse(e.toString()) ?? 0))
          : null,
      sentAt: json['sent_at'],
      respondedBy: json['responded_by'],
      resolvedAt: json['resolved_at'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      isEdited: json['is_edited'] ?? json['edited_at'] != null,
      isRead: json['is_read'] ?? false,
    );
  }

  /// هل الرسالة مرسلة فقط؟
  bool get isSent => status == 'sent';

  /// هل تم توصيل الرسالة؟
  bool get isDelivered => status == 'delivered';

  /// هل تمت قراءة الرسالة؟
  bool get isReadStatus => status == 'read';
}
