from sklearn.neighbors import KNeighborsClassifier
import cv2
import pickle
import numpy as np
import dlib
from numpy.linalg import norm
from sklearn.neighbors import NearestNeighbors
import sys

# Inisialisasi model
facedetect = cv2.CascadeClassifier('data/haarcascade_frontalface_default.xml')
smile_detector = cv2.CascadeClassifier('data/haarcascade_smile.xml')
with open('data/names.pkl', 'rb') as w:
    LABELS = pickle.load(w)
with open('data/faces.pkl', 'rb') as f:
    FACES = pickle.load(f)

knn = KNeighborsClassifier(n_neighbors=4)
knn.fit(FACES, LABELS)

similarity = NearestNeighbors(n_neighbors=30, metric='cosine', algorithm='brute', n_jobs=-1)
similarity.fit(FACES)

# Parameter deteksi kedip
threshold = 0.2
eye_closed = False
detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor("data/shape_predictor_68_face_landmarks.dat")

# Fungsi menghitung jarak garis tengah
def mid_line_distance(p1, p2, p3, p4):
    p5 = np.array([int((p1[0] + p2[0]) / 2), int((p1[1] + p2[1]) / 2)])
    p6 = np.array([int((p3[0] + p4[0]) / 2), int((p3[1] + p4[1]) / 2)])
    return norm(p5 - p6)

# Fungsi menghitung aspect ratio untuk deteksi kedip
def aspect_ratio(landmarks, eye_range):
    eye = np.array([np.array([landmarks.part(i).x, landmarks.part(i).y]) for i in eye_range])
    B = norm(eye[0] - eye[3])
    A = mid_line_distance(eye[1], eye[2], eye[5], eye[4])
    ear = A / B
    return ear

# Ambil path gambar dari argumen
image_path = sys.argv[1]
print(image_path)
# Baca gambar
frame = cv2.imread(image_path)
gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
faces = facedetect.detectMultiScale(gray, scaleFactor=1.2, minNeighbors=8, minSize=(50, 50), flags=cv2.CASCADE_SCALE_IMAGE)
print(faces)
# Deteksi kedip
rects = detector(gray, 0)
kedip = 'X'
color_kedip = (0, 0, 255)
senyum = 'X'
color_senyum = (0, 0, 255)

for rect in rects:
    landmarks = predictor(gray, rect)
    left_aspect_ratio = aspect_ratio(landmarks, range(42, 48))
    right_aspect_ratio = aspect_ratio(landmarks, range(36, 42))
    ear = (left_aspect_ratio + right_aspect_ratio) / 2.0

    if ear < threshold:
        eye_closed = True
    elif ear >= threshold and eye_closed:
        kedip = 'Berhasil'
        color_kedip = (0, 255, 0)
        eye_closed = False

# Deteksi senyum
smile = smile_detector.detectMultiScale(gray, scaleFactor=1.7, minNeighbors=50, minSize=(25, 25))
for (x, y, w, h) in smile:
    if len(smile) > 0:
        senyum = 'Berhasil'
        color_senyum = (0, 255, 0)
        cv2.rectangle(frame, (x, y), (x + w, y + h), (255, 0, 0), 1)

# Deteksi wajah dan klasifikasi
nama_pengabsen = ''
for (x, y, w, h) in faces:
    crop_img = frame[y:y + h, x:x + w, :]
    resized_img = cv2.resize(crop_img, (50, 50), interpolation=cv2.INTER_AREA).flatten().reshape(1, -1)
    print(resized_img.shape)
    output = knn.predict(resized_img)
    nbrs = similarity.kneighbors(resized_img, 2, return_distance=False)

    if nbrs[0][0] > 0:
        nama_pengabsen = output[0]
    else:
        nama_pengabsen = 'UNKNOWN'

# Output nama yang terdeteksi
print(nama_pengabsen)
