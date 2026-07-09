import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/utils/helpers.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../providers/maintenance_provider.dart';
import '../../models/maintenance_request.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تفاصيل مهمة صيانة واحدة مسندة إلى فني — عرض معلومات المهمة والمطالبة بها وتحديث الحالة وإغلاقها
class TaskDetailScreen extends StatefulWidget {
  /// معرّف المهمة المراد عرضها وإدارتها.
  final int taskId;
  const TaskDetailScreen({super.key, required this.taskId});

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

/// منطق حالة [TaskDetailScreen] — إدارة إجراءات المهمة: المطالبة والبدء والإغلاق مع الملاحظات
class _TaskDetailScreenState extends State<TaskDetailScreen> {
  final _notesController = TextEditingController();
  bool _isProcessing = false;

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  /// يبحث عن المهمة عبر [widget.taskId] من قوائم الطلبات في المزوّد.
  MaintenanceRequest? _findTask(MaintenanceProvider provider) {
    try {
      return provider.requests.firstWhere((r) => r.id == widget.taskId);
    } catch (_) {
      try {
        return provider.pendingRequests.firstWhere((r) => r.id == widget.taskId);
      } catch (_) {
        return null;
      }
    }
  }

  /// يطالب بالمهمة (تعيين الفني الحالي للطلب).
  Future<void> _claimTask() async {
    setState(() => _isProcessing = true);
    final success = await context.read<MaintenanceProvider>().claimRequest(widget.taskId);
    if (!mounted) return;
    final loc = AppLocalizations.of(context)!;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.taskAccepted)));
      context.pop();
    } else {
      setState(() => _isProcessing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.taskAcceptFailed), backgroundColor: Colors.red));
    }
  }

  /// يحدّث حالة المهمة (مثلاً من 'assigned' إلى 'in_progress').
  Future<void> _updateStatus(String status) async {
    final provider = context.read<MaintenanceProvider>();
    setState(() => _isProcessing = true);
    final success = await provider.updateStatus(widget.taskId, status);
    if (!mounted) return;
    final loc = AppLocalizations.of(context)!;
    if (success) {
      setState(() => _isProcessing = false);
      final msg = status == 'in_progress' ? loc.executionStarted : loc.statusUpdated;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
    } else {
      setState(() => _isProcessing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.statusUpdateFailed), backgroundColor: Colors.red));
    }
  }

  /// يرفض المهمة المسندة مع سبب اختياري.
  Future<void> _rejectTask() async {
    final ctl = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(AppLocalizations.of(ctx)!.rejectTask),
        content: TextField(
          controller: ctl,
          maxLines: 3,
          decoration: InputDecoration(
            hintText: AppLocalizations.of(ctx)!.reasonOptional,
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(AppLocalizations.of(ctx)!.cancel)),
          TextButton(
            onPressed: () => Navigator.pop(ctx, ctl.text),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: Text(AppLocalizations.of(ctx)!.reject),
          ),
        ],
      ),
    );
    ctl.dispose();
    if (reason == null) return;
    setState(() => _isProcessing = true);
    final success = await context.read<MaintenanceProvider>().rejectRequest(widget.taskId, reason: reason.isEmpty ? null : reason);
    if (!mounted) return;
    final loc = AppLocalizations.of(context)!;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.taskRejected)));
      context.pop();
    } else {
      setState(() => _isProcessing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.rejectFailed), backgroundColor: Colors.red));
    }
  }

  /// يُغلق المهمة مع ملاحظات الإغلاق (يتطلب ملاحظات غير فارغة).
  Future<void> _closeTask() async {
    if (_notesController.text.trim().isEmpty) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.pleaseEnterClosureNotes, isError: true);
      return;
    }
    setState(() => _isProcessing = true);
    final success = await context.read<MaintenanceProvider>().closeRequest(
      widget.taskId,
      notes: _notesController.text.trim(),
    );
    if (!mounted) return;
    final loc = AppLocalizations.of(context)!;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.taskClosed)));
      context.pop();
    } else {
      setState(() => _isProcessing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(loc.taskCloseFailed), backgroundColor: Colors.red));
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<MaintenanceProvider>();
    final task = _findTask(provider);

    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.taskDetails),
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: task == null
          ? Center(child: Text(AppLocalizations.of(context)!.taskNotFound))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: _typeColor(task.aiCategory ?? '').withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Icon(_typeIcon(task.aiCategory ?? ''), color: _typeColor(task.aiCategory ?? ''), size: 24),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(task.aiCategory ?? task.problemDescription, style: const TextStyle(
                                    fontSize: 20, fontWeight: FontWeight.bold,
                                  )),
                                  if (task.propertyTitle != null)
                                    Text(task.propertyTitle!, style: TextStyle(color: context.textSecondary)),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                              decoration: BoxDecoration(
                                color: _statusColor(task.status).withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(task.statusLabel,
                                style: TextStyle(
                                  color: _statusColor(task.status),
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(AppLocalizations.of(context)!.requestDesc, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(task.problemDescription, style: TextStyle(fontSize: 14, color: context.textSecondary, height: 1.5)),
                  const SizedBox(height: 16),
                  GlassCard(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(AppLocalizations.of(context)!.requestInfo, style: TextStyle(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),
                        _infoRow(AppLocalizations.of(context)!.requestId, '#${task.id}'),
                        _infoRow(AppLocalizations.of(context)!.createdDate, task.createdAt?.substring(0, 10) ?? '---'),
                        if (task.updatedAt != null)
                          _infoRow(AppLocalizations.of(context)!.lastUpdate, task.updatedAt!.substring(0, 10)),
                        if (task.tenantName != null)
                          _infoRow(AppLocalizations.of(context)!.tenant, task.tenantName!),
                      ],
                    ),
                  ),
                  if (task.tenantId != null) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () => context.push(AppRoutes.chat.replaceFirst(':conversationId', '${task.tenantId}')),
                        icon: const Icon(Icons.chat_bubble_outline, size: 18),
                        label: Text(AppLocalizations.of(context)!.message,
                          style: const TextStyle(fontFamily: 'Cairo'),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: MaskanColors.kBlue,
                          side: BorderSide(color: MaskanColors.kBlue.withValues(alpha: 0.3)),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                      ),
                    ),
                  ],
                  if (task.status == 'assigned' || task.status == 'in_progress') ...[
                    const SizedBox(height: 16),
                    TextField(
                      controller: _notesController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        labelText: task.status == 'in_progress' ? AppLocalizations.of(context)!.closureNotes : AppLocalizations.of(context)!.notes,
                        hintText: AppLocalizations.of(context)!.addNotesHint,
                      ),
                    ),
                  ],
                  const SizedBox(height: 24),
                  if (task.status == 'pending') ...[
                    ElevatedButton(
                      onPressed: _isProcessing ? null : _claimTask,
                      style: ElevatedButton.styleFrom(backgroundColor: MaskanColors.kBlue),
                      child: _isProcessing
                          ? const SizedBox(height: 20, width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : Text(AppLocalizations.of(context)!.acceptTask),
                    ),
                  ],
                  if (task.status == 'assigned') ...[
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton(
                            onPressed: _isProcessing ? null : () => _updateStatus('in_progress'),
                            style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                            child: Text(AppLocalizations.of(context)!.startExecution),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ElevatedButton(
                            onPressed: _isProcessing ? null : _rejectTask,
                            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                            child: Text(AppLocalizations.of(context)!.rejectTask),
                          ),
                        ),
                      ],
                    ),
                  ],
                  if (task.status == 'in_progress') ...[
                    ElevatedButton(
                      onPressed: _isProcessing ? null : _closeTask,
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                      child: _isProcessing
                          ? const SizedBox(height: 20, width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : Text(AppLocalizations.of(context)!.closeTaskCompleted),
                    ),
                  ],
                  if (task.status == 'completed') ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.green[50],
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle, color: Colors.green),
                          const SizedBox(width: 8),
                          Text(AppLocalizations.of(context)!.taskCompleted, style: TextStyle(
                            color: Colors.green, fontWeight: FontWeight.bold,
                          )),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
    );
  }

  /// يبني صفاً من تسمية وقيمة لعرض معلومات الطلب.
  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: context.textSecondary)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  /// يُرجع لوناً مرتبطاً بفئة الصيانة.
  Color _typeColor(String type) {
    switch (type) {
      case 'كهرباء':
      case 'electricity': return Colors.orange;
      case 'سباكة':
      case 'plumbing': return Colors.blue;
      case 'تكييف':
      case 'ac':
      case 'air_conditioning': return Colors.teal;
      case 'دهانات':
      case 'painting': return Colors.purple;
      case 'نجارة':
      case 'carpentry': return Colors.brown;
      default: return Colors.grey;
    }
  }

  /// يُرجع أيقونة مرتبطة بفئة الصيانة.
  IconData _typeIcon(String type) {
    switch (type) {
      case 'كهرباء':
      case 'electricity': return Icons.electrical_services;
      case 'سباكة':
      case 'plumbing': return Icons.plumbing;
      case 'تكييف':
      case 'ac':
      case 'air_conditioning': return Icons.ac_unit;
      case 'دهانات':
      case 'painting': return Icons.format_paint;
      case 'نجارة':
      case 'carpentry': return Icons.handyman;
      default: return Icons.build;
    }
  }

  /// يُرجع لوناً يُمثّل حالة المهمة.
  Color _statusColor(String status) {
    switch (status) {
      case 'pending': return Colors.grey;
      case 'assigned': return Colors.blue;
      case 'in_progress': return Colors.orange;
      case 'completed': return Colors.green;
      default: return Colors.grey;
    }
  }
}
