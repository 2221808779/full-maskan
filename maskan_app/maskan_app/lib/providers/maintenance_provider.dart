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

  /// قائمة طلبات الصيانة للمستخدم الحالي.
  List<MaintenanceRequest> get requests => _requests;
  /// قائمة طلبات الصيانة المعلقة (غير المطالب بها) المتاحة للفنيين.
  List<MaintenanceRequest> get pendingRequests => _pendingRequests;
  /// ما إذا كان طلب الشبكة قيد التنفيذ.
  bool get isLoading => _isLoading;
  /// آخر رسالة خطأ، أو null.
  String? get error => _error;

  /// يحمّل جميع طلبات الصيانة للمستخدم الحالي من API.
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

  /// يحمّل طلبات الصيانة المعلقة (غير المطالب بها) المتاحة للفنيين.
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

  /// ينشئ طلب صيانة جديد لعقار [propertyId] بـ [type] و [description] و [photos] اختيارياً.
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

  /// يرفض طلب صيانة معلق مع إمكانية إضافة سبب [reason].
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

  /// يطالب بطلب صيانة معلق بواسطة [id] (للفنيين).
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

  /// يحدّث حالة طلب صيانة محدّد بـ [id].
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

  /// يُغلِق طلب صيانة مع إمكانية إضافة [notes] و [photos].
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
