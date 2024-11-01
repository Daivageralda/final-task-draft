from sklearn.neighbors import KNeighborsClassifier
from numpy.linalg import norm
import sys
import cv2
import pickle
import numpy as np
import dlib
import os
import json
import logging

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Inisialisasi model dan detektor
try:
    facedetect = cv2.CascadeClassifier('data/haarcascade_frontalface_default.xml')
    smile_detector = cv2.CascadeClassifier('data/haarcascade_smile.xml')
    detector = dlib.get_frontal_face_detector()
    predictor = dlib.shape_predictor("data/shape_predictor_68_face_landmarks.dat")
except Exception as e:
    logging.error(f"Error loading models or detectors: {str(e)}")
    sys.exit(1)

#M Load Model Specifically
def load_model(data):
    try:
        id = data[2]
        labels_path = f'data/{id}_labels.pkl'
        faces_path = f'data/{id}_faces.pkl'

        if not os.path.exists(labels_path) or not os.path.exists(faces_path):
            raise FileNotFoundError(f"Model files not found for ID: {id}")

        # Safely load labels and faces data
        with open(labels_path, 'rb') as label_file:
            LABELS = pickle.load(label_file)
        with open(faces_path, 'rb') as face_file:
            FACES = pickle.load(face_file)

        knn = KNeighborsClassifier(n_neighbors=8)
        knn = knn.fit(FACES, LABELS)
        return knn
    except Exception as e:
        logging.error(f"Error loading model: {str(e)}")
        sys.exit(1)

# Calculate blinking
def mid_line_distance(p1, p2, p3, p4):
    p5 = np.array([int((p1[0] + p2[0]) / 2), int((p1[1] + p2[1]) / 2)])
    p6 = np.array([int((p3[0] + p4[0]) / 2), int((p3[1] + p4[1]) / 2)])
    return norm(p5 - p6)

def aspect_ratio(landmarks, eye_range):
    eye = np.array([np.array([landmarks.part(i).x, landmarks.part(i).y]) for i in eye_range])
    B = norm(eye[0] - eye[3])
    A = mid_line_distance(eye[1], eye[2], eye[5], eye[4])
    ear = A / B
    return ear


# Processing images from argument
def images_received(data):
    try:
        if len(data) > 1:
            json_file_path = data[1]
            if not os.path.exists(json_file_path) or not json_file_path.endswith('.json'):
                raise ValueError(f"Invalid JSON file path: {json_file_path}")

            with open(json_file_path, 'r') as json_file:
                image_paths = json.load(json_file)

            return True, image_paths
        else:
            logging.warning("No image data received")
            return False, []
    except Exception as e:
        logging.error(f"Error processing image data: {str(e)}")
        return False, []

def process(data):
    eye_closed = False
    blink = False
    smile = False
    name = 'Unknown People'
    threshold = 0.2

    status, images = images_received(data)
    if status:
        model = load_model(data)
        for path in images:
            if os.path.splitext(path)[-1].lower() == '.png':
                try:
                    img = cv2.imread(path, cv2.IMREAD_GRAYSCALE)
                    if img is None:
                        raise ValueError(f"Failed to read image {path}")

                    faces = facedetect.detectMultiScale(img, scaleFactor=1.3, minNeighbors=6, minSize=(50, 50), flags=cv2.CASCADE_SCALE_IMAGE)
                    logging.info(f"Processing {path}: Detected {len(faces)} faces")

                    if len(faces) > 0:
                        for (x, y, w, h) in faces:
                            crop_img = img[y:y + h, x:x + w]
                            resized_img = cv2.resize(crop_img, (50, 50), interpolation=cv2.INTER_AREA).flatten().reshape(1, -1)

                            output = model.predict(resized_img)
                            name = output[0] if output[0] != 'Unknown' else 'Unknown People'

                        rects = detector(img, 0)
                        for rect in rects:
                            landmarks = predictor(img, rect)
                            left_aspect_ratio = aspect_ratio(landmarks, range(42, 48))
                            right_aspect_ratio = aspect_ratio(landmarks, range(36, 42))
                            ear = (left_aspect_ratio + right_aspect_ratio) / 2.0

                            if ear < threshold:
                                eye_closed = True
                            elif ear >= threshold and eye_closed:
                                blink = True
                                eye_closed = False
                            else:
                                blink = False

                        smiles = smile_detector.detectMultiScale(img, scaleFactor=1.7, minNeighbors=50, minSize=(25, 25))
                        smile = len(smiles) > 0

                except Exception as e:
                    logging.error(f"Error processing image {path}: {str(e)}")
                    continue

        if name != 'Unknown People' and blink and smile:
            return name, 'Blinking', 'Smiling'
        else:
            return 'Faces Invalid'
    else:
        return 'No Data Available'

if __name__ == "__main__":
    if len(sys.argv) < 3:
        logging.error("Insufficient arguments provided. Usage: script.py <json_file> <model_id>")
        sys.exit(1)

    result = process(sys.argv)
    logging.info(f"Result: {result}")

# from sklearn.neighbors import KNeighborsClassifier
# from numpy.linalg import norm
# import sys
# import cv2
# import pickle
# import numpy as np
# import dlib
# import os
# import json

# # Inisialisasi model dan detektor
# facedetect = cv2.CascadeClassifier('data/haarcascade_frontalface_default.xml')
# smile_detector = cv2.CascadeClassifier('data/haarcascade_smile.xml')
# detector = dlib.get_frontal_face_detector()
# predictor = dlib.shape_predictor("data/shape_predictor_68_face_landmarks.dat")

# def load_model(data):
#     id = data[2]
#     with open(f'data/{id}.pkl', 'rb') as w:
#         LABELS = pickle.load(w)
#     with open(f'data/{id}.pkl', 'rb') as f:
#         FACES = pickle.load(f)

#     knn = KNeighborsClassifier(n_neighbors=8)
#     knn.fit(FACES, LABELS)

#     return knn

# # Fungsi menghitung aspect ratio untuk deteksi kedip
# def mid_line_distance(p1, p2, p3, p4):
#     p5 = np.array([int((p1[0] + p2[0]) / 2), int((p1[1] + p2[1]) / 2)])
#     p6 = np.array([int((p3[0] + p4[0]) / 2), int((p3[1] + p4[1]) / 2)])
#     return norm(p5 - p6)

# def aspect_ratio(landmarks, eye_range):
#     eye = np.array([np.array([landmarks.part(i).x, landmarks.part(i).y]) for i in eye_range])
#     B = norm(eye[0] - eye[3])
#     A = mid_line_distance(eye[1], eye[2], eye[5], eye[4])
#     ear = A / B
#     return ear

# def images_received(data):
#     if len(data) > 1:
#         json_file_path = data[1]
#         print(f"Received JSON file path: {json_file_path}")

#         with open(json_file_path, 'r') as json_file:
#             image_paths = json.load(json_file)

#         return True, image_paths
#     else:
#         return False

# # Ambil path gambar dari argumen
# def process(data):
#     eye_closed = False
#     blink = False
#     smile = False
#     name = 'Unknown People'
#     threshold = 0.2


#     status, images = images_received(data)
#     if status:
#         model = load_model(data)
#         for path in images:
#             if os.path.splitext(path)[-1].lower() == '.png':
#                 img = cv2.imread(path, cv2.IMREAD_GRAYSCALE)

#                 if img is not None:
#                     # Deteksi wajah
#                     faces = facedetect.detectMultiScale(img, scaleFactor=1.3, minNeighbors=6, minSize=(50, 50), flags=cv2.CASCADE_SCALE_IMAGE)
#                     print(f"Processing {path}: Detected {len(faces)} faces")

#                     if len(faces) > 0:
#                         for (x, y, w, h) in faces:
#                             crop_img = img[y:y + h, x:x + w]
#                             resized_img = cv2.resize(crop_img, (50, 50), interpolation=cv2.INTER_AREA).flatten().reshape(1, -1)

#                             # Prediksi nama wajah
#                             output = model.predict(resized_img)
#                             name = output[0] if output[0] != 'Unknown' else 'Unknown People'

#                         # Deteksi landmarks dan hitung aspect ratio untuk kedip
#                         rects = detector(img, 0)
#                         for rect in rects:
#                             landmarks = predictor(img, rect)
#                             left_aspect_ratio = aspect_ratio(landmarks, range(42, 48))
#                             right_aspect_ratio = aspect_ratio(landmarks, range(36, 42))
#                             ear = (left_aspect_ratio + right_aspect_ratio) / 2.0

#                             if ear < threshold:
#                                 eye_closed = True
#                             elif ear >= threshold and eye_closed:
#                                 blink = True
#                                 eye_closed = False
#                             else:
#                                 blink = False

#                         # Deteksi senyum
#                         smiles = smile_detector.detectMultiScale(img, scaleFactor=1.7, minNeighbors=50, minSize=(25, 25))
#                         smile = len(smiles) > 0

#         # Mengembalikan hasil deteksi
#         if name != 'Unknown People' and blink and smile:
#             return name, 'Blinking', 'Smiling'
#         else:
#             return 'Faces Invalid'
#     else:
#         return 'No Data Available'

# process(sys.argv)

# frame = cv2.imread(image_paths)
# gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

# Tahap front: deteksi wajah
# if stage == 'front':
#     faces = facedetect.detectMultiScale(gray, scaleFactor=1.2, minNeighbors=8, minSize=(50, 50), flags=cv2.CASCADE_SCALE_IMAGE)
#     if len(faces) > 0:
#         for (x, y, w, h) in faces:
#             crop_img = frame[y:y + h, x:x + w]
#             resized_img = cv2.resize(crop_img, (50, 50), interpolation=cv2.INTER_AREA).flatten().reshape(1, -1)
#             output = knn.predict(resized_img)
#             print(output[0])

# # Tahap blink: deteksi kedip
# elif stage == 'blink':
#     rects = detector(gray, 0)
#     for rect in rects:
#         landmarks = predictor(gray, rect)
#         left_aspect_ratio = aspect_ratio(landmarks, range(42, 48))
#         right_aspect_ratio = aspect_ratio(landmarks, range(36, 42))
#         ear = (left_aspect_ratio + right_aspect_ratio) / 2.0

#         if ear < threshold:
#             eye_closed = True
#         elif ear >= threshold and eye_closed:
#             print("Blink detected")
#             eye_closed = False
#         else:
#             print("No blink detected")

# # Tahap smile: deteksi senyum
# elif stage == 'smile':
#     smiles = smile_detector.detectMultiScale(gray, scaleFactor=1.7, minNeighbors=50, minSize=(25, 25))
#     if len(smiles) > 0:
#         print("Smile detected")
#     else:
#         print("No smile detected")
