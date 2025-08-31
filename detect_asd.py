#!/usr/bin/env python3
"""
detect_asd.py

This script is a placeholder for ASD (Autism Spectrum Disorder) facial
detection. It accepts an image file path as an argument and returns
JSON with a probability and a classification result. The current
implementation uses a simple heuristic based on the brightness of
the image: the average grayscale pixel intensity is normalized to a
probability between 0 and 1. If the average intensity exceeds 0.5,
the result is considered "ASD positive"; otherwise it is "ASD negative".

In a production environment you should replace this logic with a
trained model or API call that evaluates facial markers relevant to
ASD screening.
"""

import sys
import json
from PIL import Image
import numpy as np

def compute_probability(image_path: str) -> float:
    """Compute a dummy probability based on image brightness."""
    try:
        with Image.open(image_path) as img:
            gray = img.convert('L')
            arr = np.array(gray, dtype=np.float32)
            # Normalize pixel values to [0,1]
            arr /= 255.0
            avg = float(np.mean(arr))
            return max(0.0, min(1.0, avg))
    except Exception:
        return 0.0

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"probability": 0.0, "result": "error"}))
        return
    image_path = sys.argv[1]
    prob = compute_probability(image_path)
    # Convert probability to binary ASD/TD decision.
    # A threshold of 0.6 is used here. You can adjust this threshold
    # based on empirical model performance.
    result = "ASD" if prob >= 0.6 else "TD"
    print(json.dumps({"probability": prob, "result": result}))

if __name__ == '__main__':
    main()