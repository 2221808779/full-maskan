import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/utils/helpers.dart';
import '../../providers/complaint_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تقديم شكوى — عنوان ووصف تفصيلي مع التحقق من الحقول والإرسال عبر مزود الشكاوى
class ComplaintFormScreen extends StatefulWidget {
  const ComplaintFormScreen({super.key});

  @override
  State<ComplaintFormScreen> createState() => _ComplaintFormScreenState();
}

/// منطق حالة [ComplaintFormScreen] — إدارة حالة النموذج والتحقق من الحقول وإرسال الشكوى
class _ComplaintFormScreenState extends State<ComplaintFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  /// يتحقق من صحة النموذج ويُرسل الشكوى عبر [ComplaintProvider].
  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSubmitting = true);
    final provider = context.read<ComplaintProvider>();
    final success = await provider.submitComplaint(
      _titleController.text.trim(),
      _descriptionController.text.trim(),
    );
    if (!mounted) return;
    setState(() => _isSubmitting = false);
    if (success) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.complaintSent);
      context.pop();
    } else {
      Helpers.showSnackBar(context, provider.error ?? AppLocalizations.of(context)!.complaintFailed, isError: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.newComplaint),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              const SizedBox(height: 16),
              TextFormField(
                controller: _titleController,
                decoration: InputDecoration(
                  labelText: AppLocalizations.of(context)!.complaintTitle,
                  hintText: AppLocalizations.of(context)!.complaintSummaryHint,
                ),
                validator: (v) => v == null || v.isEmpty ? AppLocalizations.of(context)!.complaintTitleRequired : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descriptionController,
                maxLines: 6,
                decoration: InputDecoration(
                  labelText: AppLocalizations.of(context)!.complaintDetails,
                  hintText: AppLocalizations.of(context)!.complaintDetailHint,
                ),
                validator: (v) => v == null || v.isEmpty ? AppLocalizations.of(context)!.complaintDetailsRequired : null,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                child: _isSubmitting
                    ? const SizedBox(height: 20, width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(AppLocalizations.of(context)!.submitComplaint),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
