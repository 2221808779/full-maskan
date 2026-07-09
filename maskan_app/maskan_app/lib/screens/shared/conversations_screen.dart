import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../config/routes.dart';
import '../../providers/chat_provider.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض قائمة المحادثات مع مؤشرات القراءة/عدم القراءة ومعاينة آخر رسالة
/// والطوابع الزمنية ودعم السحب للحذف
class ConversationsScreen extends StatefulWidget {
  const ConversationsScreen({super.key});

  @override
  State<ConversationsScreen> createState() => _ConversationsScreenState();
}

/// منطق حالة [ConversationsScreen] — تحميل المحادثات وحذفها مع التأكيد وتنسيق عرض الوقت
class _ConversationsScreenState extends State<ConversationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ChatProvider>().loadConversations();
    });
  }

  /// يعرض مربع حوار تأكيد ويحذف المحادثة إذا تم التأكيد.
  Future<void> _deleteConversation(BuildContext context, int conversationId, String otherUserName) async {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1A2A3A) : MaskanColors.lBg,
        title: Text(loc.deleteConversationTitle, style: TextStyle(
          color: isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary,
          fontFamily: 'Cairo',
        )),
        content: Text('${loc.deleteConversationConfirm} "$otherUserName"?', style: TextStyle(
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
    if (confirmed == true && context.mounted) {
      await context.read<ChatProvider>().deleteConversation(conversationId);
    }
  }

  /// تنسيق سلسلة تاريخ إلى وقت نسبي قابل للقراءة (بالعربية).
  String _formatTime(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final dt = DateTime.parse(dateStr);
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inMinutes < 1) return 'الآن';
      if (diff.inMinutes < 60) return '${diff.inMinutes} د';
      if (diff.inHours < 24) return '${diff.inHours} س';
      if (diff.inDays < 7) return '${diff.inDays} ي';
      return '${dt.day}/${dt.month}';
    } catch (_) {
      return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ChatProvider>();
    return MaskanScaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A3A5C),
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Text(AppLocalizations.of(context)!.conversations, style: const TextStyle(
          color: Colors.white, fontFamily: 'Cairo',
        )),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.white, size: 18),
          onPressed: () => context.mounted ? Navigator.pop(context) : null,
        ),
      ),
      body: provider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : provider.conversations.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.mail_outline, color: const Color(0xFF989EA7), size: 64),
                      const SizedBox(height: 16),
                      Text(AppLocalizations.of(context)!.noConversationsYet, style: TextStyle(
                        color: const Color(0xFF989EA7), fontFamily: 'Cairo',
                      )),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: EdgeInsets.zero,
                  itemCount: provider.conversations.length,
                  separatorBuilder: (_, _) => const Divider(
                    height: 1, color: Color(0xFFEDEEF0),
                  ),
                  itemBuilder: (_, i) {
                    final c = provider.conversations[i];
                    final hasUnread = c.unreadCount > 0;
                    return InkWell(
                      onTap: () => context.push(
                        AppRoutes.chat.replaceFirst(':conversationId', '${c.id}'),
                      ),
                      onLongPress: () => _deleteConversation(context, c.id, c.otherUserName ?? ''),
                      child: Container(
                        height: 72,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        color: hasUnread ? const Color(0xFFF8F1E6).withValues(alpha: 0.5) : Colors.transparent,
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 24,
                              backgroundColor: const Color(0xFF1A3A5C),
                              backgroundImage: c.otherUserAvatar != null
                                  ? NetworkImage(AppConstants.resolveImageUrl(c.otherUserAvatar!))
                                  : null,
                              child: c.otherUserAvatar == null
                                  ? Text(
                                      c.otherUserName?[0] ?? '?',
                                      style: const TextStyle(
                                        color: Colors.white, fontFamily: 'Cairo', fontSize: 16,
                                      ),
                                    )
                                  : null,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(c.otherUserName ?? AppLocalizations.of(context)!.userFallback, style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: hasUnread ? FontWeight.w700 : FontWeight.w600,
                                          color: const Color(0xFF1A1D22), fontFamily: 'Cairo',
                                        )),
                                      ),
                                      Text(_formatTime(c.lastMessageAt), style: const TextStyle(
                                        fontSize: 11,
                                        color: Color(0xFF989EA7),
                                        fontFamily: 'Cairo',
                                      )),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          c.lastMessage ?? '',
                                          style: const TextStyle(
                                            fontSize: 13,
                                            color: Color(0xFF989EA7),
                                            fontFamily: 'Cairo',
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      if (hasUnread)
                                        Container(
                                          margin: const EdgeInsets.only(left: 8),
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF1A3A5C),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Text('${c.unreadCount}', style: const TextStyle(
                                            color: Colors.white, fontSize: 11,
                                            fontFamily: 'Cairo',
                                          )),
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
