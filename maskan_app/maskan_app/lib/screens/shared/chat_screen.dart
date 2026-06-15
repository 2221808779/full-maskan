import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/chat_provider.dart';
import '../../models/message.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../l10n/app_localizations.dart';

/// شاشة الدردشة — إرسال وتعديل وحذف الرسائل في المحادثة
/// تدعم إرسال الرسائل في الوقت الفعلي مع إشعارات القراءة والتعديل/الحذف بالضغط المطول
/// والتمرير التلقائي إلى آخر رسالة
class ChatScreen extends StatefulWidget {
  /// The ID of the conversation to open.
  final int conversationId;
  const ChatScreen({super.key, required this.conversationId});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

/// منطق حالة [ChatScreen] — إدارة إرسال الرسائل وتحريرها وحذفها وإدخال النص والتمرير
class _ChatScreenState extends State<ChatScreen> {
  final _controller = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<ChatProvider>();
      provider.loadMessages(widget.conversationId);
      provider.markAsRead(widget.conversationId);
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  int? _editingMessageId;

  /// Sends the current text as a new message or saves an edited message.
  Future<void> _send() async {
    final text = _controller.text.trim();
    if (text.isEmpty) return;
    if (_editingMessageId != null) {
      await context.read<ChatProvider>().editMessage(_editingMessageId!, text);
      _editingMessageId = null;
    } else {
      await context.read<ChatProvider>().sendMessage(widget.conversationId, text);
    }
    _controller.clear();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  /// Populates the text field with [msg] content for editing.
  void _startEdit(Message msg) {
    _editingMessageId = msg.id;
    _controller.text = msg.messageText;
    _controller.selection = TextSelection.fromPosition(TextPosition(offset: _controller.text.length));
    setState(() {});
  }

  /// Shows a confirmation dialog and deletes the message if confirmed.
  void _deleteMessage(int messageId) async {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1A2A3A) : MaskanColors.lBg,
        title: Text(loc.deleteMessageTitle, style: TextStyle(
          color: isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary,
          fontFamily: 'Cairo',
        )),
        content: Text(loc.deleteMessageConfirm, style: TextStyle(
          color: isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary,
          fontFamily: 'Cairo',
        )),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(loc.cancel, style: TextStyle(color: isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary, fontFamily: 'Cairo')),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(loc.delete, style: const TextStyle(color: MaskanColors.danger, fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      await context.read<ChatProvider>().deleteMessage(messageId);
    }
  }

  /// Formats a UTC date string into HH:mm display time.
  String _formatTime(String? createdAt) {
    if (createdAt == null) return '';
    try {
      final dt = DateTime.parse(createdAt);
      final hour = dt.hour.toString().padLeft(2, '0');
      final minute = dt.minute.toString().padLeft(2, '0');
      return '$hour:$minute';
    } catch (_) {
      return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ChatProvider>();
    final userId = context.read<AuthProvider>().user?.id ?? 0;
    final loc = AppLocalizations.of(context);

    return MaskanScaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A3A5C),
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.white, size: 18),
          onPressed: () => context.mounted ? Navigator.pop(context) : null,
        ),
        title: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: Colors.white.withValues(alpha: 0.25),
              child: const Icon(Icons.person, size: 18, color: Colors.white),
            ),
            const SizedBox(width: 10),
            Text(AppLocalizations.of(context)!.chat, style: const TextStyle(
              color: Colors.white, fontFamily: 'Cairo', fontSize: 16,
            )),
          ],
        ),
        centerTitle: true,
      ),
      body: Column(
        children: [
          Expanded(
            child: Container(
              color: const Color(0xFFE8E0D8),
              child: provider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : provider.messages.isEmpty
                      ? const _EmptyChat()
                      : ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.all(16),
                          itemCount: provider.messages.length,
                          itemBuilder: (_, i) {
                            final msg = provider.messages[i];
                            final isMe = msg.senderId == userId;
                            return _ChatBubble(
                              msg: msg,
                              isMe: isMe,
                              timeText: _formatTime(msg.createdAt),
                              onEdit: isMe ? () => _startEdit(msg) : null,
                              onDelete: isMe ? () => _deleteMessage(msg.id) : null,
                            );
                          },
                        ),
            ),
          ),
          Container(
            padding: const EdgeInsets.fromLTRB(8, 8, 12, 8),
            color: const Color(0xFFF0EBE5),
            child: SafeArea(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (_editingMessageId != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      color: const Color(0xFF1A3A5C).withValues(alpha: 0.1),
                      child: Row(
                        children: [
                          const Icon(Icons.edit, size: 14, color: Color(0xFF1A3A5C)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(loc.editingMessage, style: const TextStyle(
                              fontSize: 12, color: Color(0xFF1A3A5C), fontFamily: 'Cairo',
                            )),
                          ),
                          GestureDetector(
                            onTap: () {
                              _editingMessageId = null;
                              _controller.clear();
                              setState(() {});
                            },
                            child: const Icon(Icons.close, size: 16, color: Color(0xFF989EA7)),
                          ),
                        ],
                      ),
                    ),
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(24),
                            boxShadow: const [
                              BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 1)),
                            ],
                          ),
                          child: TextField(
                            controller: _controller,
                            style: const TextStyle(
                              color: Color(0xFF2C3138), fontFamily: 'Cairo', fontSize: 14,
                            ),
                            decoration: InputDecoration(
                              hintText: loc.typeMessage,
                              hintStyle: const TextStyle(color: Color(0xFF989EA7), fontFamily: 'Cairo'),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(24),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            ),
                            onSubmitted: (_) => _send(),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      GestureDetector(
                        onTap: _send,
                        child: Container(
                          width: 42, height: 42,
                          decoration: const BoxDecoration(
                            color: Color(0xFF1A3A5C),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// عنصر نائب عند عدم وجود رسائل في المحادثة
class _EmptyChat extends StatelessWidget {
  const _EmptyChat();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.comment, size: 48, color: const Color(0xFFA3700E).withValues(alpha: 0.7)),
          const SizedBox(height: 12),
          Text(
            AppLocalizations.of(context)!.noMessagesYet,
            style: const TextStyle(color: Color(0xFF989EA7), fontFamily: 'Cairo'),
          ),
        ],
      ),
    );
  }
}

/// فقاعة رسالة واحدة — تنسيق مختلف للإرسال والاستقبال مع دعم الضغط المطول للتحرير/الحذف
class _ChatBubble extends StatelessWidget {
  final Message msg;
  final bool isMe;
  final String timeText;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  const _ChatBubble({
    required this.msg,
    required this.isMe,
    required this.timeText,
    this.onEdit,
    this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final bgColor = isMe ? const Color(0xFFD9FDD3) : Colors.white;

    final borderRadius = isMe
        ? const BorderRadius.only(
            topLeft: Radius.circular(8),
            topRight: Radius.circular(8),
            bottomLeft: Radius.circular(8),
            bottomRight: Radius.circular(2))
        : const BorderRadius.only(
            topLeft: Radius.circular(8),
            topRight: Radius.circular(8),
            bottomRight: Radius.circular(8),
            bottomLeft: Radius.circular(2));

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: GestureDetector(
        onLongPress: isMe && (onEdit != null || onDelete != null)
            ? () => _showMenu(context)
            : null,
        child: Container(
          margin: const EdgeInsets.symmetric(vertical: 3),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.75,
          ),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: borderRadius,
            boxShadow: const [
              BoxShadow(color: Colors.black12, blurRadius: 2, offset: Offset(0, 1)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                msg.messageText,
                style: const TextStyle(
                  color: Color(0xFF2C3138),
                  fontSize: 11,
                  fontFamily: 'Cairo',
                ),
              ),
              const SizedBox(height: 3),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (msg.isEdited)
                    Padding(
                      padding: const EdgeInsets.only(right: 4),
                      child: Text(
                        'edited',
                        style: TextStyle(
                          fontSize: 7,
                          color: Colors.black45,
                          fontFamily: 'Cairo',
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ),
                  Text(
                    timeText,
                    style: const TextStyle(
                      fontSize: 7,
                      color: Color(0xFF989EA7),
                      fontFamily: 'Cairo',
                    ),
                  ),
                  if (isMe) ...[
                    const SizedBox(width: 3),
                    Icon(
                      msg.isRead ? Icons.done_all : Icons.check,
                      size: 10,
                      color: msg.isRead ? const Color(0xFF4FC3F7) : Colors.black38,
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Shows a bottom sheet with Edit/Delete options for own messages.
  void _showMenu(BuildContext context) {
    final loc = AppLocalizations.of(context);
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (onEdit != null)
              ListTile(
                leading: const Icon(Icons.edit, color: Color(0xFF1A3A5C)),
                title: Text(loc.editMessage, style: const TextStyle(fontFamily: 'Cairo')),
                onTap: () {
                  Navigator.pop(ctx);
                  onEdit!();
                },
              ),
            if (onDelete != null)
              ListTile(
                leading: const Icon(Icons.delete_outline, color: MaskanColors.danger),
                title: Text(loc.deleteMessage, style: const TextStyle(fontFamily: 'Cairo')),
                onTap: () {
                  Navigator.pop(ctx);
                  onDelete!();
                },
              ),
          ],
        ),
      ),
    );
  }
}
