import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';
import '../../config/colors.dart';

// مؤشر تحميل Shimmer مع عدد قابل للتعديل من العناصر الشبيهة بالبطاقات
class LoadingWidget extends StatelessWidget {
  /// ارتفاع كل بطاقة Shimmer placeholder.
  final double height;

  /// عدد عناصر Shimmer المعروضة.
  final int itemCount;

  const LoadingWidget({super.key, this.height = 120, this.itemCount = 3});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: MaskanColors.kGlassBorder,
      highlightColor: MaskanColors.kBgInput,
      child: ListView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: itemCount,
        itemBuilder: (_, _) => Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
          height: height,
          decoration: BoxDecoration(
            color: MaskanColors.kBgCard,
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
    );
  }
}

/// عنصر نائب Shimmer يحاكي تخطيط [PropertyCard]
class PropertyCardShimmer extends StatelessWidget {
  const PropertyCardShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: MaskanColors.kGlassBorder,
      highlightColor: MaskanColors.kBgInput,
      child: Card(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                width: 120, height: 100,
                decoration: BoxDecoration(
                  color: MaskanColors.kBgCard,
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(height: 14, width: 150, color: MaskanColors.kBgCard),
                    const SizedBox(height: 8),
                    Container(height: 12, width: 100, color: MaskanColors.kBgCard),
                    const SizedBox(height: 8),
                    Container(height: 12, width: 80, color: MaskanColors.kBgCard),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
