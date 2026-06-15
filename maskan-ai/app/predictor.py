"""منتج التنبؤات — LSTM للتنبؤ بطلبات الصيانة المستقبلية بناءً على التاريخ"""
import numpy as np
from datetime import datetime, timedelta


class LSTMPredictor:
    """منتج LSTM — تحميل نموذج TensorFlow والتنبؤ باحتمالية طلب الصيانة"""
    def __init__(self, model_path: str, categories: list):
        self.categories = categories
        self.model = None
        self.loaded = False
        self._load(model_path)

    def _load(self, model_path: str):
        try:
            import tensorflow as tf
            self.model = tf.keras.models.load_model(model_path)
            self.loaded = True
            print("LSTM predictor loaded")
        except Exception as e:
            print(f"LSTM model not available: {e}")
            self.loaded = False

    def predict(self, history: list) -> dict | None:
        if not self.loaded or len(history) < 3:
            return None

        # Use LSTM only for days prediction
        items = history[-10:]
        seq = [[item["days_ago"] / 365.0, item["category_id"] / 6.0]
               for item in items]
        while len(seq) < 10:
            seq.insert(0, [0.0, 0.0])
        sequence = np.array(seq, dtype=np.float32).reshape(1, 10, 2)

        outputs = self.model.predict(sequence, verbose=0)
        if isinstance(outputs, list):
            days_out = outputs[0][0]
        else:
            days_out = outputs[0][0]

        days_next = max(1, int(round(float(days_out[0]) * 365)))

        # Category: use the most recent category from history
        last_cat_id = history[-1]["category_id"]
        cat_idx = max(0, min(last_cat_id - 1, len(self.categories) - 1))
        category = self.categories[cat_idx]
        pred_date = (datetime.now() + timedelta(days=days_next)).strftime("%Y-%m-%d")

        return {
            "predicted_category": category,
            "predicted_category_id": cat_idx + 1,
            "days_until_next": days_next,
            "predicted_date": pred_date,
        }


lstm_predictor_instance = None
