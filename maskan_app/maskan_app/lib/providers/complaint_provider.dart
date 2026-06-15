import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';

/// يدير تقديم الشكاوى وعرض قائمة شكاوى المستخدم
/// يتواصل مع API الشكاوى لتقديم شكوى جديدة وتحميل الشكاوى السابقة
class ComplaintProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<Map<String, dynamic>> _complaints = [];
  bool _isLoading = false;
  String? _error;

  /// The list of user complaints as raw JSON maps.
  List<Map<String, dynamic>> get complaints => _complaints;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;
  /// The last error message, or null.
  String? get error => _error;

  /// Loads all complaints for the current user from the API.
  Future<void> loadComplaints() async {
    _isLoading = true;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.complaints);
      final List<dynamic> list = response.data['data'] ?? response.data['complaints'] ?? [];
      _complaints = list.map((j) => Map<String, dynamic>.from(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Submits a new complaint with [title], [description], and optional [photos].
  Future<bool> submitComplaint(String title, String description, {List<String>? photos}) async {
    _isLoading = true;
    notifyListeners();
    try {
      await _api.post(ApiEndpoints.complaints, data: {
        'title': title,
        'description': description,
        'photos': photos ?? [],
      });
      _isLoading = false;
      notifyListeners();
      await loadComplaints();
      return true;
    } catch (e) {
      _error = 'فشل إرسال الشكوى';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
