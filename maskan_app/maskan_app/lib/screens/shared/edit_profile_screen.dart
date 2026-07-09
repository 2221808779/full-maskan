import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/colors.dart';
import '../../config/constants.dart';
import '../../providers/auth_provider.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../l10n/app_localizations.dart';

/// شاشة تعديل الملف الشخصي — تعديل الاسم ورقم الهاتف والصورة الشخصية
/// تدعم التقاط صورة من الكاميرا أو المعرض ورفعها وحذف الصورة الحالية وحفظ التغييرات
class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

/// منطق حالة [EditProfileScreen] — إدارة عناصر التحكم في النموذج واختيار الصور وتحديث الملف الشخصي
class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  bool _isLoading = false;
  final ImagePicker _picker = ImagePicker();
  String? _imagePath;
  bool _uploadingPhoto = false;

  /// يفتح الكاميرا لالتقاط صورة شخصية جديدة.
  Future<void> _pickFromCamera() async {
    final file = await _picker.pickImage(source: ImageSource.camera, maxWidth: 1024);
    if (file != null) {
      setState(() => _imagePath = file.path);
      _uploadPhoto();
    }
  }

  /// يفتح المعرض لاختيار صورة شخصية جديدة.
  Future<void> _pickFromGallery() async {
    final file = await _picker.pickImage(source: ImageSource.gallery, maxWidth: 1024);
    if (file != null) {
      setState(() => _imagePath = file.path);
      _uploadPhoto();
    }
  }

  /// يحذف الصورة الشخصية الحالية عبر [AuthProvider.deletePhoto].
  Future<void> _deletePhoto() async {
    final auth = context.read<AuthProvider>();
    final success = await auth.deletePhoto();
    if (!mounted) return;
    if (success) {
      setState(() => _imagePath = null);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)!.photoDeleted, style: TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kSuccess.withValues(alpha: 0.9),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error ?? AppLocalizations.of(context)!.photoDeleteFailed, style: TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kBgCard,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  /// يرفع ملف الصورة المحدد عبر [AuthProvider.uploadPhoto].
  Future<void> _uploadPhoto() async {
    if (_imagePath == null) return;
    setState(() => _uploadingPhoto = true);
    final auth = context.read<AuthProvider>();
    final success = await auth.uploadPhoto(_imagePath!);
    if (!mounted) return;
    setState(() => _uploadingPhoto = false);
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context)!.photoUpdated, style: TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kSuccess.withValues(alpha: 0.9),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.error ?? AppLocalizations.of(context)!.photoUpdateFailed, style: TextStyle(fontFamily: 'Cairo')),
          backgroundColor: MaskanColors.kBgCard,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _nameController = TextEditingController(text: user?.fullName ?? '');
    _phoneController = TextEditingController(text: user?.phone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  /// يتحقق من صحة النموذج ويحفظ تغييرات الاسم/الهاتف عبر [AuthProvider.updateProfile].
  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    try {
      final auth = context.read<AuthProvider>();
      final success = await auth.updateProfile(
        _nameController.text.trim(),
        _phoneController.text.trim(),
      );
      if (!mounted) return;
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.profileUpdated, style: TextStyle(fontFamily: 'Cairo')),
            backgroundColor: MaskanColors.kSuccess.withValues(alpha: 0.9),
            behavior: SnackBarBehavior.floating,
          ),
        );
        context.pop();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(auth.error ?? AppLocalizations.of(context)!.profileUpdateFailed, style: TextStyle(fontFamily: 'Cairo')),
            backgroundColor: MaskanColors.kBgCard,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios, color: context.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text(AppLocalizations.of(context)!.editProfile, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              const SizedBox(height: 16),
              Stack(
                children: [
                      CircleAvatar(
                    radius: 48,
                    backgroundColor: MaskanColors.kBlue,
                    backgroundImage: (_imagePath != null)
                        ? FileImage(File(_imagePath!))
                        : (context.read<AuthProvider>().user?.profileImage != null
                            ? NetworkImage(AppConstants.resolveImageUrl(context.read<AuthProvider>().user!.profileImage!))
                            : null),
                    child: _uploadingPhoto
                        ? const CircularProgressIndicator(color: Colors.white, strokeWidth: 3)
                        : (context.read<AuthProvider>().user?.profileImage == null && _imagePath == null)
                            ? Text(
                                (_nameController.text.isNotEmpty ? _nameController.text[0] : '?'),
                                style: const TextStyle(fontSize: 36, color: Colors.white, fontFamily: 'Cairo'),
                              )
                            : null,
                  ),
                  Positioned(
                    bottom: 0, right: 0,
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: MaskanColors.kGold,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.camera_alt, size: 16, color: Colors.white),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () {
                  showModalBottomSheet(
                    context: context,
                    backgroundColor: MaskanColors.kBgCard,
                    shape: const RoundedRectangleBorder(
                      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                    ),
                    builder: (_) => SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(AppLocalizations.of(context)!.changePhoto, style: TextStyle(
                              color: context.textPrimary, fontFamily: 'Cairo',
                            )),
                            const SizedBox(height: 16),
                            ListTile(
                              leading: const Icon(Icons.camera_alt, color: MaskanColors.kBlue),
                              title: Text(AppLocalizations.of(context)!.camera, style: TextStyle(fontFamily: 'Cairo')),
                              onTap: () { Navigator.pop(context); _pickFromCamera(); },
                            ),
                            ListTile(
                              leading: const Icon(Icons.photo_library, color: MaskanColors.kBlue),
                              title: Text(AppLocalizations.of(context)!.gallery, style: TextStyle(fontFamily: 'Cairo')),
                              onTap: () { Navigator.pop(context); _pickFromGallery(); },
                            ),
                            const Divider(color: MaskanColors.kGlassBorder),
                            ListTile(
                              leading: const Icon(Icons.delete_outline, color: MaskanColors.kDanger),
                              title: Text(AppLocalizations.of(context)!.deletePhoto, style: TextStyle(
                                fontFamily: 'Cairo', color: MaskanColors.kDanger,
                              )),
                              onTap: () { Navigator.pop(context); _deletePhoto(); },
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
                child: Text(AppLocalizations.of(context)!.changePhoto, style: TextStyle(
                  color: MaskanColors.kBlue, fontFamily: 'Cairo',
                )),
              ),
              const SizedBox(height: 24),
              GlassCard(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                borderRadius: 20,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(AppLocalizations.of(context)!.fullName, style: TextStyle(
                      fontSize: 13, color: context.textSecondary, fontFamily: 'Cairo',
                    )),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _nameController,
                      style: TextStyle(
                        color: context.textPrimary, fontFamily: 'Cairo', fontSize: 15,
                      ),
                      decoration: InputDecoration(
                        filled: true, fillColor: MaskanColors.kBgInput,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kGlassBorder, width: 0.5),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kGlassBorder, width: 0.5),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kBlue, width: 1),
                        ),
                        prefixIcon: const Icon(Icons.person, color: MaskanColors.kBlue, size: 20),
                      ),
                      validator: (v) => v == null || v.trim().isEmpty ? AppLocalizations.of(context)!.enterName : null,
                    ),
                    const SizedBox(height: 16),
                    Text(AppLocalizations.of(context)!.phoneNumber, style: TextStyle(
                      fontSize: 13, color: context.textSecondary, fontFamily: 'Cairo',
                    )),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      style: TextStyle(
                        color: context.textPrimary, fontFamily: 'Cairo', fontSize: 15,
                      ),
                      decoration: InputDecoration(
                        filled: true, fillColor: MaskanColors.kBgInput,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kGlassBorder, width: 0.5),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kGlassBorder, width: 0.5),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: MaskanColors.kBlue, width: 1),
                        ),
                        prefixIcon: Icon(Icons.lock_outline, color: context.textMuted, size: 16),
                        suffixIcon: const Icon(Icons.phone_android, color: MaskanColors.kBlue, size: 20),
                      ),
                      validator: (v) {
                        if (v == null || v.trim().isEmpty) return AppLocalizations.of(context)!.enterPhone;
                        if (!RegExp(r'^09[12348]\d{7}$').hasMatch(v.trim())) return AppLocalizations.of(context)!.phoneInvalidLibyan;
                        return null;
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              PrimaryButton(
                label: AppLocalizations.of(context)!.save,
                isLoading: _isLoading,
                onPressed: _save,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
