import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/favorite.dart';

/// يدير العقارات المفضلة للمستخدم
/// يقوم بتحميل المفضلات وتبديل حالة الإعجاب والتحقق من حالة العقار
/// ويحتفظ بمجموعة من معرفات العقارات المفضلة في الذاكرة للوصول السريع
class FavoriteProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Favorite> _favorites = [];
  Set<int> _favoriteIds = {};
  bool _isLoading = false;

  /// قائمة العناصر المفضلة.
  List<Favorite> get favorites => _favorites;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;

  /// يحمّل جميع المفضلات من API ويحدّث مجموعة البحث بالمعرفات.
  Future<void> loadFavorites() async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.favorites);
      final List<dynamic> list = response.data['data'] ?? [];
      _favorites = list.map((j) => Favorite.fromJson(j)).toList();
      _favoriteIds = _favorites.map((f) => f.propertyId).toSet();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// يبدّل حالة الإعجاب لعقار محدّد بـ [propertyId].
  Future<bool> toggleFavorite(int propertyId) async {
    try {
      await _api.post(ApiEndpoints.toggleFavorite, data: {
        'property_id': propertyId,
      });
      if (_favoriteIds.contains(propertyId)) {
        _favoriteIds.remove(propertyId);
        _favorites.removeWhere((f) => f.propertyId == propertyId);
      } else {
        _favoriteIds.add(propertyId);
        _favorites.insert(0, Favorite(propertyId: propertyId));
      }
      notifyListeners();
      return true;
    } catch (e) {
      debugPrint('toggleFavorite error: $e');
      return false;
    }
  }

  /// يتحقق عبر API ما إذا كان العقار مفضّلاً، ويعيد النتيجة.
  Future<bool> check(int propertyId) async {
    try {
      final response = await _api.post(ApiEndpoints.checkFavorite, data: {
        'property_id': propertyId,
      });
      final bool isFav = response.data['is_favorited'] ?? response.data['data'] ?? false;
      if (isFav) {
        _favoriteIds.add(propertyId);
      } else {
        _favoriteIds.remove(propertyId);
      }
      notifyListeners();
      return isFav;
    } catch (e) {
      return _favoriteIds.contains(propertyId);
    }
  }

  /// يُرجِع ما إذا كان العقار مُفضّلاً حالياً (بناءً على الحالة المحلية).
  bool isFavorite(int propertyId) => _favoriteIds.contains(propertyId);
}
