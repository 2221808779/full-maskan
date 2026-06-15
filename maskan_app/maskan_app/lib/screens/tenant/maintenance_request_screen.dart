import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/loading_widget.dart';
import '../../core/widgets/empty_state.dart';
import '../../providers/maintenance_provider.dart';
import '../../models/maintenance_request.dart';
import '../../l10n/app_localizations.dart';

/// شاشة قائمة طلبات الصيانة للمستأجر — مؤشرات الحالة وأزرار إنشاء طلب جديد مع أيقونات الفئات ومعلومات الفني
class MaintenanceRequestScreen extends StatefulWidget {
  const MaintenanceRequestScreen({super.key});

  @override
  State<MaintenanceRequestScreen> createState() => _MaintenanceRequestScreenState();
}

/// منطق حالة [MaintenanceRequestScreen] — تحميل طلبات الصيانة وعرض كل طلب كبطاقة منسقة
class _MaintenanceRequestScreenState extends State<MaintenanceRequestScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MaintenanceProvider>().loadRequests();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<MaintenanceProvider>();
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.maintenanceRequests),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: MaskanColors.kBlue,
        child: const Icon(Icons.add, color: Colors.white),
        onPressed: () => context.push(AppRoutes.maintenanceForm.replaceAll(':propertyId', '0')),
      ),
      body: provider.isLoading
          ? const LoadingWidget()
          : provider.requests.isEmpty
              ? EmptyState(icon: Icons.build_outlined, title: AppLocalizations.of(context)!.noMaintenanceRequests,
                  subtitle: AppLocalizations.of(context)!.sendRequestForActiveBooking,
                  actionLabel: AppLocalizations.of(context)!.newRequest,
                  onAction: _onCreateRequest,
                )
              : RefreshIndicator(
                  onRefresh: () => provider.loadRequests(),
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: provider.requests.length,
                    itemBuilder: (_, i) => _requestCard(provider.requests[i]),
                  ),
                ),
    );
  }

  /// Navigates to the maintenance form screen to create a new request.
  void _onCreateRequest() => context.push(AppRoutes.maintenanceForm.replaceAll(':propertyId', '0'));

  /// Builds a card for a single [MaintenanceRequest] showing category,
  /// description, status badge, and assigned technician.
  Widget _requestCard(MaintenanceRequest req) {
    return GlassCard(
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: _typeColor(req.aiCategory ?? '').withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(_typeIcon(req.aiCategory ?? ''), color: _typeColor(req.aiCategory ?? ''), size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(req.aiCategory ?? req.problemDescription, style: const TextStyle(fontWeight: FontWeight.bold)),
                      if (req.propertyTitle != null)
                        Text(req.propertyTitle!, style: TextStyle(fontSize: 12, color: context.textSecondary)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _statusColor(req.status).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(req.statusLabel,
                    style: TextStyle(color: _statusColor(req.status), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(req.problemDescription, maxLines: 2, overflow: TextOverflow.ellipsis,
              style: TextStyle(color: context.textSecondary, fontSize: 13),
            ),
            if (req.technicianName != null) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.person, size: 14, color: context.textSecondary),
                  const SizedBox(width: 4),
                  Text('${AppLocalizations.of(context)!.technicianLabel}${req.technicianName}', style: TextStyle(fontSize: 12, color: context.textSecondary)),
                ],
              ),
            ],
          ],
        ),
    );
  }

  /// Returns a color associated with the maintenance category type.
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

  /// Returns an icon associated with the maintenance category type.
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

  /// Returns a color representing the request status.
  Color _statusColor(String status) {
    switch (status) {
      case 'assigned': return Colors.blue;
      case 'in_progress': return Colors.orange;
      case 'completed': return Colors.green;
      default: return Colors.grey;
    }
  }
}
