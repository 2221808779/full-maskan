import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/review.dart';

/// يدير التقييمات للعقارات والفنيين والمالكين
/// يدعم تحميل تقييمات عقار أو فني معين، وإرسال تقييمات جديدة مع نجوم وتعليقات
class ReviewProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Review> _reviews = [];
  List<Review> _technicianReviews = [];
  bool _isLoading = false;

  /// The list of reviews for the currently viewed property.
  List<Review> get reviews => _reviews;
  /// The list of reviews for a specific technician.
  List<Review> get technicianReviews => _technicianReviews;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;

  /// Loads all reviews for a given property by its [propertyId].
  Future<void> loadPropertyReviews(int propertyId) async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.propertyReviews(propertyId));
      final List<dynamic> list = response.data['data'] ?? [];
      _reviews = list.map((j) => Review.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Loads all reviews for a technician by their [technicianId].
  Future<void> loadTechnicianReviews(int technicianId) async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get('/reviews', queryParameters: {
        'technician_id': technicianId,
      });
      final List<dynamic> list = response.data['data'] ?? [];
      _technicianReviews = list.map((j) => Review.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Submits a review with a star rating and optional comment.
  ///
  /// [targetId] and [targetType] identify the review target
  /// ('property', 'technician', or 'owner'). Returns null on success
  /// or an error message string on failure.
  Future<String?> submitReview({
    required int targetId,
    required String targetType,
    required int stars,
    String? comment,
    int? bookingId,
    int? propertyId,
  }) async {
    try {
      final data = <String, dynamic>{
        'stars': stars,
        'comment': comment ?? '',
      };
      if (targetType == 'property') {
        data['property_id'] = targetId;
        if (bookingId != null) data['booking_id'] = bookingId;
      } else if (targetType == 'technician') {
        data['technician_id'] = targetId;
        data['property_id'] = propertyId;
      } else if (targetType == 'owner') {
        data['owner_id'] = targetId;
        data['property_id'] = propertyId;
      }

      await _api.post(ApiEndpoints.reviews, data: data);
      if (targetType == 'property' && targetId == propertyId) {
        await loadPropertyReviews(targetId);
      }
      notifyListeners();
      return null;
    } on DioException catch (e) {
      final msg = e.response?.data is Map
          ? (e.response!.data as Map)['message'] ?? e.response!.data['error'] ?? e.message
          : e.message;
      debugPrint('submitReview Dio error: $msg');
      return msg.toString();
    } catch (e) {
      debugPrint('submitReview error: $e');
      return e.toString();
    }
  }
}
