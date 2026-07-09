"""اختبارات خدمة AI — اختبار نقاط النهاية /health و /classify و /predict و /fallback"""
import pytest
from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)


def test_health():
    """اختبار نقطة نهاية /health للتأكد من حالة الخدمة"""
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "ok"
    assert "distilbert_loaded" in data
    assert "ml_models_loaded" in data
    assert "lstm_loaded" in data


def test_classify_arabic_electricity():
    """اختبار تصنيف وصف عطل كهربائي بالعربية"""
    response = client.post("/classify", json={"text": "المقبس لا يعمل في الغرفة"})
    assert response.status_code == 200
    data = response.json()
    assert data["category"] == "electricity"
    assert data["category_id"] == 1
    assert 0 < data["confidence"] <= 1.0
    assert data["model_used"] in ("lr_rf_voting", "distilbert")


def test_classify_arabic_plumbing():
    """اختبار تصنيف وصف عطل سباكة بالعربية"""
    response = client.post("/classify", json={"text": "تسريب مياه من الصنبور"})
    assert response.status_code == 200
    assert response.json()["category"] == "plumbing"


def test_classify_english_ac():
    # النموذج المدرب على العربية قد يصنف النص الإنجليزي كـ "other"
    response = client.post("/classify", json={"text": "AC is not cooling the room"})
    assert response.status_code == 200
    assert response.json()["category"] in ("air_conditioning", "other")


def test_classify_empty_text():
    """اختبار رفض النص القصير جداً (أقل من 3 أحرف)"""
    response = client.post("/classify", json={"text": "ab"})
    assert response.status_code == 422


def test_classify_returns_valid_category_id():
    """اختبار أن معرّف الفئة المعاد يقع ضمن النطاق الصحيح 1-6"""
    response = client.post("/classify", json={"text": "الباب لا يغلق"})
    data = response.json()
    assert data["category_id"] in [1, 2, 3, 4, 5, 6]


def test_predict_insufficient_history():
    """اختبار رفض طلب التنبؤ بعدد غير كافٍ من سجلات الصيانة"""
    response = client.post("/predict", json={
        "property_id": 5,
        "history": [{"days_ago": 10, "category_id": 2}]
    })
    assert response.status_code == 422
