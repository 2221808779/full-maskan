import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../core/widgets/empty_state.dart';
import '../../core/widgets/loading_widget.dart';
import '../../core/widgets/notification_bell.dart';
import '../../providers/maintenance_provider.dart';
import '../../providers/notification_provider.dart';
import '../../models/maintenance_request.dart';
import '../../core/design/design_tokens.dart';
import '../../l10n/app_localizations.dart';

/// الشاشة الرئيسية للفني — تعرض المهام المسندة والإحصائيات (قيد الانتظار، قيد التنفيذ، مكتملة) وقائمة المهام
/// تقوم بتحميل طلبات الصيانة وعدد الإشعارات غير المقروءة عند التهيئة
class TechnicianHomeScreen extends StatefulWidget {
  const TechnicianHomeScreen({super.key});

  @override
  State<TechnicianHomeScreen> createState() => _TechnicianHomeScreenState();
}

/// منطق حالة [TechnicianHomeScreen] — إدارة تحميل المهام وحساب الإحصائيات والتنقل إلى التفاصيل
class _TechnicianHomeScreenState extends State<TechnicianHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MaintenanceProvider>().loadRequests();
      context.read<NotificationProvider>().loadUnreadCount();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<MaintenanceProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final accentColor = isDark ? MaskanColors.kBlueSky : MaskanColors.kBlue;
    final loc = AppLocalizations.of(context);

    final pending = provider.requests.where((r) => r.status == 'pending' || r.status == 'assigned').length;
    final inProgress = provider.requests.where((r) => r.status == 'in_progress').length;
    final completedCount = provider.requests.where((r) => r.status == 'completed').length;

    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
              child: Row(
                children: [
                  SvgPicture.asset('assets/images/house-icon.svg', width: 24, height: 24,
                    colorFilter: ColorFilter.mode(accentColor, BlendMode.srcIn),
                  ),
                  const SizedBox(width: 8),
                  Text(AppConstants.appName, style: TextStyle(
                    fontSize: 18, fontWeight: FontWeight.bold, color: accentColor,
                  )),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.chat_bubble_outline, color: MaskanColors.kBlue),
                    onPressed: () => context.push(AppRoutes.conversations),
                  ),
                  NotificationBell(iconSize: 22),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 0),
              child: Text(loc.manageAssignedTasks, style: TextStyle(
                fontSize: 13, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted,
              )),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              child: Row(
                children: [
                  Expanded(child: _statCard(loc.newStatus, pending, const Color(0xFF4A90D9))),
                  const SizedBox(width: 10),
                  Expanded(child: _statCard(loc.inProgress, inProgress, const Color(0xFFE67E22))),
                  const SizedBox(width: 10),
                  Expanded(child: _statCard(loc.completed, completedCount, const Color(0xFF27AE60))),
                ],
              ),
            ),
            Expanded(
              child: provider.isLoading && provider.requests.isEmpty
                  ? const LoadingWidget()
                  : provider.requests.isEmpty
                      ? EmptyState(
                          icon: Icons.assignment_turned_in_rounded,
                          title: loc.noTasksCurrently,
                          subtitle: loc.tasksWillAppearHere,
                        )
                      : RefreshIndicator(
                          onRefresh: provider.loadRequests,
                          child: ListView.builder(
                            padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
                            itemCount: provider.requests.length,
                            itemBuilder: (_, i) => _taskCard(provider.requests[i]),
                          ),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  /// Builds a statistics card showing a count and label with a tint color.
  Widget _statCard(String label, int count, Color color) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: isDark ? MaskanColors.kBgCard2 : const Color(0xF0FFFFFF),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isDark ? color.withValues(alpha: 0.2) : color.withValues(alpha: 0.15)),
        boxShadow: [
          BoxShadow(
            color: isDark ? Colors.transparent : Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 4),
      child: Column(
        children: [
          Text('$count', style: TextStyle(
            fontSize: 22, fontWeight: FontWeight.bold, color: color,
          )),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(
            fontSize: 11, fontWeight: FontWeight.w500,
            color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted,
            fontFamily: 'Cairo',
          )),
        ],
      ),
    );
  }

  /// Builds a task card for a [MaintenanceRequest] with property title,
  /// description, status badge, date, and category.
  Widget _taskCard(MaintenanceRequest req) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final tint = _statusColor(req.status);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 5),
      decoration: BoxDecoration(
        color: isDark ? MaskanColors.kBgCard2 : const Color(0xF0FFFFFF),
        borderRadius: BorderRadius.circular(14),
        boxShadow: isDark
            ? DesignTokens.softShadowDark()
            : [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: () => context.push(AppRoutes.taskDetail.replaceAll(':id', '${req.id}')),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(14, 14, 14, 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (req.propertyTitle != null)
                            Text(req.propertyTitle!,
                              style: TextStyle(
                                fontSize: 15, fontWeight: FontWeight.w600,
                                color: isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary,
                              )),
                          const SizedBox(height: 4),
                          Text(req.problemDescription, maxLines: 2, overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 12, height: 1.4,
                              color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted,
                            )),
                        ],
                      ),
                    ),
                    const SizedBox(width: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: tint.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(req.statusLabel,
                        style: TextStyle(
                          fontSize: 11, fontWeight: FontWeight.w600, color: tint,
                          fontFamily: 'Cairo',
                        )),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Icon(Icons.calendar_today_rounded, size: 11, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                    const SizedBox(width: 4),
                    Text(req.createdAt?.substring(0, 10) ?? '---',
                      style: TextStyle(fontSize: 11, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                    ),
                    const SizedBox(width: 16),
                    Icon(Icons.category_outlined, size: 11, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                    const SizedBox(width: 4),
                    Text(_categoryLabel(req.aiCategory),
                      style: TextStyle(fontSize: 11, color: isDark ? MaskanColors.kTextMuted : MaskanColors.lTextMuted),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  /// Returns a localized label for the maintenance category.
  String _categoryLabel(String? category) {
    switch (category) {
      case 'كهرباء':
      case 'electricity': return AppLocalizations.of(context)!.electricity;
      case 'سباكة':
      case 'plumbing': return AppLocalizations.of(context)!.plumbing;
      case 'تكييف':
      case 'ac':
      case 'air_conditioning': return AppLocalizations.of(context)!.ac;
      case 'دهانات':
      case 'painting': return AppLocalizations.of(context)!.painting;
      case 'نجارة':
      case 'carpentry': return AppLocalizations.of(context)!.carpentry;
      default: return AppLocalizations.of(context)!.generalMaintenance;
    }
  }

  /// Returns a color representing the task status.
  Color _statusColor(String status) {
    switch (status) {
      case 'pending': return Colors.grey;
      case 'assigned': return const Color(0xFF4A90D9);
      case 'in_progress': return const Color(0xFFE67E22);
      case 'completed': return const Color(0xFF27AE60);
      default: return Colors.grey;
    }
  }
}