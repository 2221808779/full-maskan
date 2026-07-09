"""مصنف أعطال الصيانة — BERT + TF-IDF + Random Forest + Logistic Regression مع تجاوز تلقائي"""
import joblib
import numpy as np
from app.config import DISTILBERT_DIR, VECTORIZER_PATH, LR_PATH, RF_PATH, CATEGORIES


class BERTClassifier:
    """مصنف BERT — تحميل نموذج DistilBERT وتصنيف وصف العطل إلى فئة"""
    def __init__(self, model_dir: str, categories: list):
        self.categories = categories
        self.tokenizer = None
        self.model = None
        self.loaded = False
        self._load(model_dir)

    def _load(self, model_dir: str):
        """تحميل نموذج DistilBERT المُدرّب مسبقاً من المسار المحدد"""
        try:
            from transformers import DistilBertTokenizer, DistilBertForSequenceClassification
            import torch
            self.tokenizer = DistilBertTokenizer.from_pretrained(model_dir)
            self.model = DistilBertForSequenceClassification.from_pretrained(model_dir)
            self.device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
            self.model.to(self.device)
            self.model.eval()
            self.loaded = True
            print("DistilBERT loaded")
        except Exception as e:
            print(f"DistilBERT not available: {e}")
            self.loaded = False

    def classify(self, text: str) -> dict:
        """تصنيف نص عطل باستخدام نموذج DistilBERT وإرجاع الفئة والثقة"""
        import torch
        import torch.nn.functional as F

        # ترميز النص وتحويله إلى توترات (tensors) مع حشو واقتطاع
        inputs = self.tokenizer(
            text, return_tensors="pt", truncation=True,
            max_length=128, padding=True
        ).to(self.device)

        # تمرير النموذج بدون حساب التدرج لتسريع الاستنتاج
        with torch.no_grad():
            logits = self.model(**inputs).logits

        # تحويل المخرجات إلى احتمالات واختيار أعلى فئة
        probs = F.softmax(logits, dim=-1)[0]
        pred_idx = probs.argmax().item()
        confidence = probs[pred_idx].item()
        category = self.categories[pred_idx]

        return {
            "category": category,
            "confidence": round(confidence, 2),
            "category_id": pred_idx + 1,
            "model_used": "distilbert",
        }


class MLClassifier:
    """مصنف تعلم آلي تقليدي — Logistic Regression + Random Forest كخيار احتياطي"""
    def __init__(self, vectorizer_path, lr_path, rf_path, categories):
        self.categories = categories
        self.vectorizer = None
        self.lr_model = None
        self.rf_model = None
        self.loaded = False
        self._load(vectorizer_path, lr_path, rf_path)

    def _load(self, vec_path, lr_path, rf_path):
        """تحميل نماذج التعلم الآلي الاحتياطية (TF-IDF + Logistic Regression + Random Forest)"""
        try:
            self.vectorizer = joblib.load(vec_path)
            self.lr_model = joblib.load(lr_path)
            self.rf_model = joblib.load(rf_path)
            self.loaded = True
            print("ML fallback models loaded (LR + RF)")
        except Exception as e:
            print(f"ML models not available: {e}")
            self.loaded = False

    def classify(self, text: str) -> dict:
        """تصنيف نص عطل باستخدام متوسط احتمالات Logistic Regression و Random Forest"""
        # تحويل النص إلى متجه TF-IDF
        X = self.vectorizer.transform([text])

        # الحصول على الاحتمالات من كلا النموذجين
        lr_proba = self.lr_model.predict_proba(X)[0]
        rf_proba = self.rf_model.predict_proba(X)[0]

        # متوسط الاحتمالات كنظام تصويت بين النموذجين
        avg_proba = (lr_proba + rf_proba) / 2
        pred_idx = int(np.argmax(avg_proba))
        confidence = float(avg_proba[pred_idx])
        category = self.categories[pred_idx]

        return {
            "category": category,
            "confidence": round(confidence, 2),
            "category_id": pred_idx + 1,
            "model_used": "lr_rf_voting",
        }


class HybridClassifier:
    """مصنف هجين — يستخدم BERT أولاً ويتحول تلقائياً لنماذج ML إذا كان BERT غير متاح"""
    def __init__(self):
        self.bert_classifier = BERTClassifier(DISTILBERT_DIR, CATEGORIES)
        self.ml_classifier = MLClassifier(VECTORIZER_PATH, LR_PATH, RF_PATH, CATEGORIES)

    @property
    def is_loaded(self) -> bool:
        return self.bert_classifier.loaded or self.ml_classifier.loaded

    def classify(self, text: str) -> dict:
        """تصنيف النص باستخدام BERT إن وجد، وإلا يستخدم النماذج التقليدية كخيار احتياطي"""
        # استخدام BERT كخيار أول إذا كان متاحاً
        if self.bert_classifier.loaded:
            return self.bert_classifier.classify(text)
        # الرجوع إلى نماذج ML إذا كان BERT غير متاح
        if self.ml_classifier.loaded:
            return self.ml_classifier.classify(text)
        raise RuntimeError("No AI models are loaded. Run training scripts first.")


hybrid_classifier = HybridClassifier()
