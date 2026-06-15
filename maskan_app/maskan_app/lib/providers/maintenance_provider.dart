import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/api/api_endpoints.dart';
import '../models/maintenance_request.dart';

/// يدير طلبات الصيانة — الإنشاء، المطالبة (للأخذ)، وتحديث الحالة
/// يدعم تحميل جميع الطلبات، تحميل الطلبات المعلقة، إنشاء طلبات جديدة،
/// المطالبة/رفض الطلبات، وتحديث الحالة
class MaintenanceProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient();

  List<MaintenanceRequest> _requests = [];
  List<MaintenanceRequest> _pendingRequests = [];
  bool _isLoading = false;
  String? _error;

  /// The list of maintenance requests for the current user.
  List<MaintenanceRequest> get requests => _requests;
  /// The list of pending (unclaimed) maintenance requests available for technicians.
  List<MaintenanceRequest> get pendingRequests => _pendingRequests;
  /// Whether a network request is in progress.
  bool get isLoading => _isLoading;
  /// The last error message, or null.
  String? get error => _error;

  /// Loads all maintenance requests for the current user from the API.
  Future<void> loadRequests() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.maintenanceRequests);
      final List<dynamic> list = response.data['data'] ?? [];
      _requests = list.map((j) => MaintenanceRequest.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = 'فشل تحميل طلبات الصيانة';
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Loads pending (unclaimed) maintenance requests available for technicians.
  Future<void> loadPendingRequests() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final response = await _api.get(ApiEndpoints.pendingMaintenance);
      final List<dynamic> list = response.data['data'] ?? [];
      _pendingRequests = list.map((j) => MaintenanceRequest.fromJson(j)).toList();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = 'فشل تحميل الطلبات المتاحة';
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Creates a new maintenance request for a [propertyId] with [type], [description], and optional [photos].
  Future<bool> createRequest({
    required int propertyId,
    required String type,
    required String description,
    List<String>? photos,
  }) async {
    _isLoading = true;
    notifyListeners();
    try {
      await _api.post(ApiEndpoints.maintenanceRequests, data: {
        'property_id': propertyId,
        'type': type,
        'description': description,
        'photos': photos ?? [],
      });
      _isLoading = false;
      notifyListeners();
      await loadRequests();
      return true;
    } catch (e) {
      _error = 'فشل إرسال الطلب';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Rejects a pending maintenance request with an optional [reason].
  Future<bool> rejectRequest(int id, {String? reason}) async {
    try {
      final data = <String, dynamic>{};
      if (reason != null) data['reason'] = reason;
      await _api.put(ApiEndpoints.rejectMaintenance(id), data: data);
      await loadRequests();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// Claims a pending maintenance request by its [id] (for technicians).
  Future<bool> claimRequest(int id) async {
    try {
      await _api.put(ApiEndpoints.claimMaintenance(id));
      await loadPendingRequests();
      await loadRequests();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// Updates the status of a maintenance request identified by [id].
  Future<bool> updateStatus(int id, String status) async {
    try {
      await _api.put(ApiEndpoints.updateMaintenanceStatus(id), data: {
        'status': status,
      });
      await loadRequests();
      return true;
    } catch (e) {
      return false;
    }
  }

  /// Closes a maintenance request with optional [notes] and [photos].
  Future<bool> closeRequest(int id, {String? notes, List<String>? photos}) async {
    try {
      final data = <String, dynamic>{};
      if (notes != null) data['notes'] = notes;
      if (photos != null) data['photos'] = photos;
      await _api.put(ApiEndpoints.closeMaintenance(id), data: data);
      await loadRequests();
      return true;
    } catch (e) {
      return false;
    }
  }
}
