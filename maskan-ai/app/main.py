"""مدخل خدمة AI للصيانة — FastAPI مع نقاط نهاية للتصنيف والتنبؤ والصحة"""
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import json
import os

from app.schemas import (
    ClassifyRequest, ClassifyResponse,
    PredictRequest, PredictResponse, HealthResponse,
)
from app.classifier import hybrid_classifier
from app.predictor import LSTMPredictor
from app.config import VERSION, LSTM_PATH, CATEGORIES, REDIS_URL, CACHE_TTL

app = FastAPI(
    title="Maskan AI Service",
    description="خدمة NLP: تصنيف أعطال الصيانة + تنبؤ دوري",
    version=VERSION,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "http://127.0.0.1:8000"],
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

lstm_predictor = LSTMPredictor(LSTM_PATH, CATEGORIES)

cache = None
# محاولة الاتصال بـ Redis للتخزين المؤقت
try:
    import redis as rd
    cache = rd.from_url(REDIS_URL, decode_responses=True)
    cache.ping()
    print("Redis connected")
except Exception:
    # في حال فشل الاتصال، تعطيل التخزين المؤقت
    cache = None
    print("Redis not available — caching disabled")


@app.get("/health", response_model=HealthResponse, tags=["System"])
def health_check():
    """التحقق من حالة الخدمة وحالة تحميل جميع النماذج"""
    return HealthResponse(
        status="ok",
        distilbert_loaded=hybrid_classifier.bert_classifier.loaded,
        ml_models_loaded=hybrid_classifier.ml_classifier.loaded,
        lstm_loaded=lstm_predictor.loaded,
        version=VERSION,
    )


@app.post("/classify", response_model=ClassifyResponse, tags=["Classification"])
def classify(request: ClassifyRequest):
    """تصنيف وصف عطل الصيانة إلى فئة باستخدام المصنف الهجين"""
    if not hybrid_classifier.is_loaded:
        raise HTTPException(
            status_code=503,
            detail="No AI models loaded. Run training scripts first."
        )

    # التحقق من وجود نتيجة مخبأة في Redis
    cache_key = f"classify:{hash(request.text)}"
    if cache:
        cached = cache.get(cache_key)
        if cached:
            return ClassifyResponse(**json.loads(cached))

    # تنفيذ التصنيف ومعالجة أي خطأ
    try:
        result = hybrid_classifier.classify(request.text)
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

    # تخزين النتيجة في Redis مدة TTL المحددة
    if cache:
        cache.setex(cache_key, CACHE_TTL, json.dumps(result))

    return ClassifyResponse(**result)


@app.post("/predict", response_model=PredictResponse, tags=["Prediction"])
def predict(request: PredictRequest):
    """التنبؤ بموعد الصيانة القادمة والفئة المتوقعة بناءً على تاريخ الصيانة"""
    if not lstm_predictor.loaded:
        raise HTTPException(
            status_code=503,
            detail="LSTM model not loaded. Run scripts/train_predictor.py first."
        )

    # تحويل بيانات الطلب إلى قاموس للتنبؤ
    history = [item.model_dump() for item in request.history]
    result = lstm_predictor.predict(history)

    # التحقق من كفاية البيانات للتنبؤ
    if not result:
        raise HTTPException(
            status_code=422,
            detail="Insufficient history. Provide at least 3 records."
        )

    return PredictResponse(property_id=request.property_id, **result)
