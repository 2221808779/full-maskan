"""مخططات (Schemas) الطلبات والردود لخدمة AI باستخدام Pydantic"""
from pydantic import BaseModel, Field, ConfigDict
from typing import List, Optional


class ClassifyRequest(BaseModel):
    """طلب تصنيف — نص وصف العطل المراد تصنيفه"""
    model_config = ConfigDict(protected_namespaces=())
    text: str = Field(..., min_length=3, max_length=1000,
                      description="Maintenance issue description (Arabic or English)")


class ClassifyResponse(BaseModel):
    """رد التصنيف — الفئة المتوقعة ودرجة الثقة ومعرّف الفئة"""
    model_config = ConfigDict(protected_namespaces=())
    category: str = Field(..., description="Predicted fault category")
    confidence: float = Field(..., description="Confidence score 0.0 to 1.0")
    category_id: int = Field(..., description="Category ID matching specialties table")
    model_used: str = Field("", description="Model that produced this result")


class MaintenanceHistoryItem(BaseModel):
    """عنصر تاريخ الصيانة — عدد الأيام منذ حدوث العطل ومعرّف الفئة"""
    days_ago: int = Field(..., ge=0, description="Days since this fault occurred")
    category_id: int = Field(..., ge=1, le=6, description="Fault category ID")


class PredictRequest(BaseModel):
    """طلب تنبؤ — معرّف العقار وعدد الأيام للتنبؤ المستقبلي"""
    property_id: int
    history: List[MaintenanceHistoryItem] = Field(..., min_length=3,
              description="At least 3 past maintenance records")


class PredictResponse(BaseModel):
    """رد التنبؤ — قائمة التنبؤات مع التاريخ والفئة والاحتمالية"""
    model_config = ConfigDict(protected_namespaces=())
    property_id: int
    predicted_category: str
    predicted_category_id: int
    days_until_next: int
    predicted_date: str


class HealthResponse(BaseModel):
    """رد حالة الخدمة — حالة تحميل النماذج وإصدار API"""
    model_config = ConfigDict(protected_namespaces=())
    status: str = "ok"
    distilbert_loaded: bool
    ml_models_loaded: bool
    lstm_loaded: bool
    version: str = "1.0.0"
