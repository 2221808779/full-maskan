import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/utils/helpers.dart';
import '../../providers/maintenance_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تقديم طلب صيانة جديد — اختيار فئة المشكلة وكتابة وصف تفصيلي وإرسال الطلب
class MaintenanceFormScreen extends StatefulWidget {
  /// معرّف العقار المطلوب له الصيانة.
  final int propertyId;
  const MaintenanceFormScreen({super.key, required this.propertyId});

  @override
  State<MaintenanceFormScreen> createState() => _MaintenanceFormScreenState();
}

/// منطق حالة [MaintenanceFormScreen] — إدارة التحقق من النموذج واختيار النوع وإرسال طلب الصيانة
class _MaintenanceFormScreenState extends State<MaintenanceFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descriptionController = TextEditingController();
  String _selectedType = 'كهرباء';
  bool _isSubmitting = false;

  final _types = ['كهرباء', 'سباكة', 'تكييف', 'دهانات', 'نجارة', 'أخرى'];

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  /// يتحقق من صحة النموذج ويُرسل طلب الصيانة إلى المزوّد.
  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSubmitting = true);
    final provider = context.read<MaintenanceProvider>();
    final success = await provider.createRequest(
      propertyId: widget.propertyId,
      type: _selectedType,
      description: _descriptionController.text.trim(),
    );
    if (!mounted) return;
    setState(() => _isSubmitting = false);
    if (success) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.maintenanceRequestSent);
      context.pop();
    } else {
      Helpers.showSnackBar(context, provider.error ?? AppLocalizations.of(context)!.maintenanceRequestFailed, isError: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.newRequest),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(AppLocalizations.of(context)!.maintenanceType, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8, runSpacing: 8,
                children: _types.map((type) => ChoiceChip(
                  label: Text(type == 'كهرباء' ? AppLocalizations.of(context)!.electricity :
                      type == 'سباكة' ? AppLocalizations.of(context)!.plumbing :
                      type == 'تكييف' ? AppLocalizations.of(context)!.acMaintenance :
                      type == 'دهانات' ? AppLocalizations.of(context)!.painting :
                      type == 'نجارة' ? AppLocalizations.of(context)!.carpentry :
                      AppLocalizations.of(context)!.other),
                  selected: _selectedType == type,
                  selectedColor: MaskanColors.kBlue.withValues(alpha: 0.15),
                  onSelected: (v) => setState(() => _selectedType = type),
                )).toList(),
              ),
              const SizedBox(height: 20),
              TextFormField(
                controller: _descriptionController,
                maxLines: 5,
                decoration: InputDecoration(
                  labelText: AppLocalizations.of(context)!.requestDesc,
                  hintText: AppLocalizations.of(context)!.describeProblemHint,
                ),
                validator: (v) => v == null || v.isEmpty ? AppLocalizations.of(context)!.descriptionRequired : null,
              ),
              const SizedBox(height: 16),
              Card(
                color: Colors.blue[50],
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      const Icon(Icons.info, color: Colors.blue, size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(AppLocalizations.of(context)!.willNotifyRequestStatus,
                          style: const TextStyle(fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                child: _isSubmitting
                    ? const SizedBox(height: 20, width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(AppLocalizations.of(context)!.submit),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
