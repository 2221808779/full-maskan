import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../config/colors.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/utils/helpers.dart';
import '../../providers/review_provider.dart';
import '../../l10n/app_localizations.dart';

/// شاشة إرسال تقييم (تقييم بالنجوم + تعليق) لعقار أو مالك أو فني — باستخدام شريط التقييم
class ReviewFormScreen extends StatefulWidget {
  /// نوع الكيان الذي يتم تقييمه: 'property' أو 'owner' أو 'technician'.
  final String targetType;

  /// معرّف الكيان الهدف الذي يتم تقييمه.
  final int targetId;

  /// معرّف عقار اختياري للسياق (مفيد عند تقييم مالك).
  final int? propertyId;

  /// معرّف حجز اختياري لربط التقييم بحجز معيّن.
  final int? bookingId;
  const ReviewFormScreen({
    super.key,
    required this.targetType,
    required this.targetId,
    this.propertyId,
    this.bookingId,
  });

  @override
  State<ReviewFormScreen> createState() => _ReviewFormScreenState();
}

/// منطق حالة [ReviewFormScreen] — إدارة اختيار التقييم
  /// إدخال التعليق، وإرسال التقييم.
class _ReviewFormScreenState extends State<ReviewFormScreen> {
  /// تقييم النجوم الحالي (0–5، يدعم نصف النجوم).
  double _rating = 0;

  /// وحدة تحكم حقل نص تعليق التقييم.
  final _commentController = TextEditingController();

  /// ما إذا كان التقييم قيد الإرسال حالياً.
  bool _isSubmitting = false;

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  /// يتحقق من صحة التقييم ويُرسل المراجعة عبر [ReviewProvider].
  Future<void> _submit() async {
    if (_rating == 0) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.pleaseSelectRating, isError: true);
      return;
    }
    setState(() => _isSubmitting = true);
    final provider = context.read<ReviewProvider>();
    final error = await provider.submitReview(
      targetId: widget.targetId,
      targetType: widget.targetType,
      stars: _rating.toInt(),
      comment: _commentController.text.trim(),
      propertyId: widget.propertyId,
      bookingId: widget.bookingId,
    );
    if (!mounted) return;
    setState(() => _isSubmitting = false);
    if (error == null) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.reviewSubmitted);
      context.pop();
    } else {
      Helpers.showSnackBar(context, error, isError: true);
    }
  }

  /// يُرجع تسمية محلّية تصف الكيان الذي يتم تقييمه.
  String get _targetLabel {
    switch (widget.targetType) {
      case 'property': return AppLocalizations.of(context)!.propertyTarget;
      case 'owner': return AppLocalizations.of(context)!.ownerTarget;
      case 'technician': return AppLocalizations.of(context)!.technicianTarget;
      default: return widget.targetType;
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.addReview),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            const SizedBox(height: 40),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: MaskanColors.kBlue.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.star_half, size: 64, color: MaskanColors.kGold),
            ),
            const SizedBox(height: 24),
            Text('${AppLocalizations.of(context)!.rate}$_targetLabel', style: const TextStyle(
              fontSize: 22, fontWeight: FontWeight.bold,
            )),
            const SizedBox(height: 8),
            Text(AppLocalizations.of(context)!.shareYourExperience,
              style: TextStyle(fontSize: 14, color: context.textSecondary),
            ),
            const SizedBox(height: 32),
            RatingBar.builder(
              initialRating: _rating,
              minRating: 1,
              direction: Axis.horizontal,
              allowHalfRating: true,
              itemCount: 5,
              itemSize: 48,
              itemBuilder: (_, _) => const Icon(Icons.star, color: Colors.amber),
              onRatingUpdate: (v) => setState(() => _rating = v),
            ),
            const SizedBox(height: 8),
            Text(
              _rating == 0 ? AppLocalizations.of(context)!.yourRating :
              _rating <= 2 ? AppLocalizations.of(context)!.bad :
              _rating <= 3 ? 'متوسط' :
              _rating <= 4 ? AppLocalizations.of(context)!.good : AppLocalizations.of(context)!.excellent,
              style: TextStyle(fontSize: 16, color: context.textSecondary),
            ),
            const SizedBox(height: 32),
            TextField(
              controller: _commentController,
              maxLines: 4,
              decoration: InputDecoration(
                labelText: AppLocalizations.of(context)!.yourComment,
                hintText: AppLocalizations.of(context)!.writeReviewHint,
              ),
            ),
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: _isSubmitting ? null : _submit,
              child: _isSubmitting
                  ? const SizedBox(height: 20, width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(AppLocalizations.of(context)!.submitReview),
            ),
          ],
        ),
      ),
    );
  }
}
