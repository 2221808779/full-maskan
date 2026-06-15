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

  /// The list of favorite items.
  List<Favorite> get favorites => _favorites;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;

  /// Loads all favorites from the API and updates the ID lookup set.
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

  /// Toggles the favorite status of a property identified by [propertyId].
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

  /// Checks with the API whether a property is favorited, and returns the result.
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

  /// Returns whether a property is currently favorited (based on local state).
  bool isFavorite(int propertyId) => _favoriteIds.contains(propertyId);
}
