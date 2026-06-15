import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/property.dart';

/// يدير قوائم العقارات — تحميل العقارات، جلب التفاصيل، والبحث
/// يدعم التحميل المتصفح (Pagination) للعقارات، جلب تفاصيل عقار فردي،
/// والبحث/التصفية حسب معايير مختلفة
class PropertyProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Property> _properties = [];
  Property? _selectedProperty;
  bool _isLoading = false;
  String? _error;
  bool _pendingRefresh = false;
  int _currentPage = 1;
  int _lastPage = 1;
  bool _hasMore = true;

  /// The paginated list of loaded properties.
  List<Property> get properties => _properties;
  /// The currently selected/detailed property.
  Property? get selectedProperty => _selectedProperty;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;
  /// The last error message, or null.
  String? get error => _error;
  /// Whether there are more pages to load.
  bool get hasMore => _hasMore;

  /// Loads a page of properties from the API.
  ///
  /// If [refresh] is true, resets the pagination and reloads from page 1.
  Future<void> loadProperties({bool refresh = false}) async {
    if (_isLoading) {
      if (refresh) {
        _pendingRefresh = true;
      }
      return;
    }
    if (refresh || _properties.isEmpty) {
      _properties = [];
      _currentPage = 1;
      _hasMore = true;
    }
    if (!_hasMore) return;

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(
        ApiEndpoints.properties,
        queryParameters: {'page': _currentPage},
      );
      final data = response.data;
      final List<dynamic> list = data['data'] ?? [];
      _properties.addAll(list.map((j) => Property.fromJson(j)));
      _currentPage = (data['current_page'] ?? 1) + 1;
      _lastPage = data['last_page'] ?? 1;
      _hasMore = _currentPage <= _lastPage;
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      debugPrint('⚠️ loadProperties error: $e');
      _error = 'فشل تحميل العقارات';
      _isLoading = false;
      notifyListeners();
    }

    if (_pendingRefresh) {
      _pendingRefresh = false;
      await loadProperties(refresh: true);
    }
  }

  /// Loads the full detail of a single property by its [id].
  Future<void> loadPropertyDetail(int id) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(ApiEndpoints.propertyDetail(id));
      final data = response.data;
      _selectedProperty = Property.fromJson(data['data'] ?? data);
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      debugPrint('⚠️ loadPropertyDetail error: $e');
      _error = 'فشل تحميل تفاصيل العقار';
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Searches properties matching the given [query], price range, [bedrooms], and [city].
  ///
  /// Returns the filtered list directly (does not modify the main properties list).
  Future<List<Property>> searchProperties({
    String? query,
    double? minPrice,
    double? maxPrice,
    int? bedrooms,
    String? city,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (query != null) params['search'] = query;
      if (minPrice != null) params['min_price'] = minPrice;
      if (maxPrice != null) params['max_price'] = maxPrice;
      if (bedrooms != null) params['bedrooms'] = bedrooms;
      if (city != null) params['city'] = city;
      final response = await _api.get(ApiEndpoints.properties, queryParameters: params);
      final List<dynamic> list = response.data['data'] ?? [];
      return list.map((j) => Property.fromJson(j)).toList();
    } catch (e) {
      return [];
    }
  }
}
