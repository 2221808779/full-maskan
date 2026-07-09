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

  /// القائمة المقسمة إلى صفحات من العقارات المحمّلة.
  List<Property> get properties => _properties;
  /// العقار المحدد حالياً / التفصيلي.
  Property? get selectedProperty => _selectedProperty;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;
  /// آخر رسالة خطأ، أو null.
  String? get error => _error;
  /// ما إذا كان هناك المزيد من الصفحات لتحميلها.
  bool get hasMore => _hasMore;

  /// يحمّل صفحة من العقارات من API.
  ///
  /// إذا كان [refresh] true، يُعيد تعيين التصفح ويُحمّل من الصفحة 1.
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

  /// يحمّل التفاصيل الكاملة لعقار واحد بواسطة [id].
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

  /// يبحث عن عقارات تطابق [query] المحدد، نطاق السعر، [bedrooms]، و [city].
  ///
  /// يُرجِع القائمة المصفاة مباشرة (لا يعدّل قائمة العقارات الرئيسية).
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
