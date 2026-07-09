import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/booking.dart';

/// يدير عمليات الحجز — إنشاء، تحميل، عرض، وإلغاء الحجوزات
/// يتواصل مع API الحجوزات في Laravel لجلب وعرض الحجوزات
/// يوفر [fetchBlockedDates] لجلب التواريخ المحجوزة لعرضها في التقويم
class BookingProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Booking> _bookings = [];
  bool _isLoading = false;
  String? _error;

  /// قائمة الحجوزات للمستخدم الحالي.
  List<Booking> get bookings => _bookings;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;
  /// آخر رسالة خطأ، أو null.
  String? get error => _error;

  /// يجلب التواريخ المحجوزة/المظللة لعقار [propertyId] معين لعرضها في تقويم الحجز.
  ///
  /// يُرجِع قائمة من كائنات [DateTime] تمثل التواريخ غير المتاحة.
  Future<List<DateTime>> fetchBlockedDates(int propertyId) async {
    try {
      final response = await _api.get(ApiEndpoints.propertyBlackoutDates(propertyId));
      final List<dynamic> data = response.data['data'] ?? response.data ?? [];
      final dates = data.map((d) {
        if (d is String) return DateTime.tryParse(d);
        if (d is Map) {
          final dateStr = d['date'] ?? d['blackout_date'] ?? d['start_date'];
          return DateTime.tryParse(dateStr.toString());
        }
        return null;
      }).whereType<DateTime>().toList();
      return dates;
    } catch (_) {
      return [];
    }
  }

  /// يحمّل جميع الحجوزات للمستخدم الحالي من API.
  Future<void> loadBookings() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.bookings);
      final List<dynamic> list = response.data['data'] ?? [];
      _bookings = list.map((j) => Booking.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = 'فشل تحميل الحجوزات';
      _isLoading = false;
      notifyListeners();
    }
  }

  int? _lastCreatedBookingId;
  /// معرف آخر حجز تم إنشاؤه، أو null.
  int? get lastCreatedBookingId => _lastCreatedBookingId;

  /// ينشئ حجزاً جديداً لعقار محدد.
  Future<int?> createBooking({
    required int propertyId,
    required DateTime startDate,
    required DateTime endDate,
    int guests = 1,
    String? notes,
    String? propertyTitle,
    String? propertyImage,
    String? propertyAddress,
    double totalPrice = 0.0,
  }) async {
    _isLoading = true;
    _error = null;
    _lastCreatedBookingId = null;
    notifyListeners();
    try {
      final response = await _api.post(ApiEndpoints.bookings, data: {
        'property_id': propertyId,
        'start_date': startDate.toIso8601String().split('T')[0],
        'end_date': endDate.toIso8601String().split('T')[0],
        'guests': guests,
        'notes': notes ?? '',
        'total_price': totalPrice,
      });
      _isLoading = false;
      notifyListeners();
      await loadBookings();
      final bookingData = response.data['booking'];
      _lastCreatedBookingId = bookingData is Map ? bookingData['id'] as int? : null;
      return _lastCreatedBookingId;
    } catch (e) {
      String msg = 'فشل إنشاء الحجز';
      if (e is DioException) {
        debugPrint('createBooking DioException: ${e.response?.statusCode} ${e.response?.data}');
        if (e.response?.data is Map) {
          msg = (e.response!.data as Map)['message']?.toString() ?? msg;
          final errors = (e.response!.data as Map)['errors'];
          if (errors is Map && errors.isNotEmpty) {
            final firstError = errors.values.first;
            if (firstError is List && firstError.isNotEmpty) {
              msg = firstError.first.toString();
            }
          }
        }
      } else {
        debugPrint('createBooking exception: $e');
      }
      _error = msg;
      _isLoading = false;
      notifyListeners();
      return null;
    }
  }

  /// يلغي حجزاً بواسطة [id].
  Future<bool> cancelBooking(int id) async {
    try {
      await _api.put(ApiEndpoints.cancelBooking(id));
      await loadBookings();
      return true;
    } catch (e) {
      _error = 'فشل إلغاء الحجز';
      notifyListeners();
      return false;
    }
  }
}
