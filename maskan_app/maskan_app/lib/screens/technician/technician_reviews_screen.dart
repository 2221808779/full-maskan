import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import '../../config/colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/review_provider.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض جميع التقييمات التي تلقاها الفني — تحميل التقييمات وعرض كل تقييم مع النجوم والتعليق وصورة المُقيّم
class TechnicianReviewsScreen extends StatefulWidget {
  const TechnicianReviewsScreen({super.key});

  @override
  State<TechnicianReviewsScreen> createState() => _TechnicianReviewsScreenState();
}

/// منطق حالة [TechnicianReviewsScreen] — تحميل تقييمات الفني وعرضها بقائمة وبطاقات زجاجية
class _TechnicianReviewsScreenState extends State<TechnicianReviewsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.user != null) {
        context.read<ReviewProvider>().loadTechnicianReviews(auth.user!.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final loc = AppLocalizations.of(context);
    final provider = context.watch<ReviewProvider>();
    final reviews = provider.technicianReviews;

    return MaskanScaffold(
      appBar: AppBar(
        elevation: 0, scrolledUnderElevation: 0, centerTitle: true,
        title: Text(loc.reviews, style: TextStyle(
          color: context.textPrimary, fontFamily: 'Cairo',
        )),
      ),
      body: provider.isLoading && reviews.isEmpty
        ? const Center(child: CircularProgressIndicator())
        : reviews.isEmpty
        ? Center(child: Text(loc.noReviews, style: TextStyle(
            color: context.textSecondary, fontFamily: 'Cairo', fontSize: 16,
          )))
        : ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: reviews.length,
            itemBuilder: (_, i) {
              final r = reviews[i];
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: GlassCard(
                  softMode: true,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 18,
                            backgroundColor: MaskanColors.kBlue,
                            child: Text(
                              (r.reviewerName ?? '?')[0],
                              style: const TextStyle(color: Colors.white, fontFamily: 'Cairo'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(r.reviewerName ?? loc.anonymous,
                              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: context.textPrimary, fontFamily: 'Cairo'),
                            ),
                          ),
                          if (r.createdAt != null)
                            Text(r.createdAt!, style: TextStyle(fontSize: 11, color: context.textMuted, fontFamily: 'Cairo')),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          RatingBarIndicator(
                            rating: r.stars.toDouble(),
                            itemSize: 20,
                            itemBuilder: (_, _) => const Icon(Icons.star, color: MaskanColors.kGold),
                          ),
                          const SizedBox(width: 8),
                          Text('${r.stars}/5', style: TextStyle(fontSize: 14, color: context.textSecondary, fontFamily: 'Cairo')),
                        ],
                      ),
                      if (r.comment != null && r.comment!.isNotEmpty) ...[
                        const SizedBox(height: 10),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: context.textPrimary.withValues(alpha: 0.03),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(r.comment!, style: TextStyle(fontSize: 14, color: context.textPrimary, fontFamily: 'Cairo')),
                        ),
                      ],
                    ],
                  ),
                ),
              );
            },
          ),
    );
  }
}
