import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/message.dart';
import '../models/conversation.dart';

/// يدير المحادثات والرسائل بين المستخدمين
/// يتواصل مع API المحادثات لتحميل المحادثات والرسائل وإرسال رسائل جديدة
/// وإدارة دورة حياة الرسائل والمحادثات
class ChatProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Conversation> _conversations = [];
  List<Message> _messages = [];
  bool _isLoading = false;
  String? _error;

  /// قائمة المحادثات للمستخدم الحالي.
  List<Conversation> get conversations => _conversations;
  /// قائمة الرسائل في المحادثة النشطة حالياً.
  List<Message> get messages => _messages;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;
  /// آخر رسالة خطأ، أو null.
  String? get error => _error;

  /// يحمّل قائمة المحادثات من API.
  Future<void> loadConversations() async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.conversations);
      final List<dynamic> list = response.data['data'] ?? [];
      _conversations = list.map((j) => Conversation.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// يحمّل الرسائل لـ [conversationId] المحدد.
  Future<void> loadMessages(int conversationId) async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.conversationMessages(conversationId));
      final List<dynamic> list = response.data['data'] ?? [];
      _messages = list.map((j) => Message.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// يرسل رسالة بمحتوى [content] إلى [conversationId] المحدد.
  Future<bool> sendMessage(int conversationId, String content) async {
    try {
      final response = await _api.post(ApiEndpoints.messages, data: {
        'conversation_id': conversationId,
        'message': content,
      });
      final msg = Message.fromJson(response.data['data'] ?? response.data);
      _messages.add(msg);
      notifyListeners();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// يعدّل رسالة موجودة محددة بـ [messageId] بمحتوى [content] جديد.
  Future<bool> editMessage(int messageId, String content) async {
    try {
      await _api.put(ApiEndpoints.editMessage(messageId), data: {
        'message': content,
      });
      final index = _messages.indexWhere((m) => m.id == messageId);
      if (index != -1) {
        _messages[index] = Message(
          id: _messages[index].id,
          conversationId: _messages[index].conversationId,
          senderId: _messages[index].senderId,
          receiverId: _messages[index].receiverId,
          messageText: content,
          isEdited: true,
        );
        notifyListeners();
      }
      return true;
    } catch (e) {
      return false;
    }
  }

  /// يحذف رسالة بواسطة [messageId]. إذا كان [forEveryone] true، يُحذف للجميع.
  Future<bool> deleteMessage(int messageId, {bool forEveryone = false}) async {
    try {
      await _api.delete(ApiEndpoints.deleteMessage(messageId));
      _messages.removeWhere((m) => m.id == messageId);
      notifyListeners();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// يحذف محادثة كاملة وجميع رسائلها بواسطة [conversationId].
  Future<bool> deleteConversation(int conversationId) async {
    try {
      await _api.delete(ApiEndpoints.deleteConversation(conversationId));
      _conversations.removeWhere((c) => c.id == conversationId);
      notifyListeners();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// يعلّم جميع الرسائل من [otherUserId] كمقروءة.
  Future<void> markAsRead(int otherUserId) async {
    try {
      await _api.post(ApiEndpoints.markAsRead(otherUserId));
    } catch (_) {}
  }
}
