"""إعدادات خدمة AI — مسارات النماذج والفئات وإعدادات Redis"""
import os

BASE_DIR = os.path.dirname(os.path.dirname(__file__))
MODELS_DIR = os.path.join(BASE_DIR, 'models')

DISTILBERT_DIR = os.path.join(MODELS_DIR, 'distilbert_classifier')

VECTORIZER_PATH = os.path.join(MODELS_DIR, 'tfidf_vectorizer.pkl')
LR_PATH = os.path.join(MODELS_DIR, 'logistic_regression.pkl')
RF_PATH = os.path.join(MODELS_DIR, 'random_forest.pkl')

LSTM_PATH = os.path.join(MODELS_DIR, 'lstm_predictor.h5')

CATEGORY_MAP = {
    'electricity': 1,
    'plumbing': 2,
    'air_conditioning': 3,
    'painting': 4,
    'carpentry': 5,
    'other': 6,
}
CATEGORIES = list(CATEGORY_MAP.keys())

HOST = "0.0.0.0"
PORT = 8001
VERSION = "1.0.0"

REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/1")
CACHE_TTL = 3600
