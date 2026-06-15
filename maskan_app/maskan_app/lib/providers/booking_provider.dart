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

  /// The list of bookings for the current user.
  List<Booking> get bookings => _bookings;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;
  /// The last error message, or null.
  String? get error => _error;

  /// Fetches blocked/blackout dates for a given [propertyId] to display in the booking calendar.
  ///
  /// Returns a list of [DateTime] objects representing unavailable dates.
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

  /// Loads all bookings for the current user from the API.
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
  /// The ID of the most recently created booking, or null.
  int? get lastCreatedBookingId => _lastCreatedBookingId;

  /// Creates a new booking for a specified property.
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

  /// Cancels a booking by its [id].
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
