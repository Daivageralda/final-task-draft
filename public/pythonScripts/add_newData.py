import sys
import json
import numpy as np
import cv2
import os
import pickle

# Fungsi untuk memuat dan memvalidasi JSON file dari path
def load_image_paths(json_file_path):
    if not os.path.exists(json_file_path):
        raise FileNotFoundError(f"JSON file {json_file_path} not found.")
    with open(json_file_path, 'r') as json_file:
        return json.load(json_file)

# Fungsi untuk memuat cascade classifier dan memastikan berhasil di-load
def load_face_cascade(cascade_path):
    face_cascade = cv2.CascadeClassifier(cascade_path)
    if face_cascade.empty():
        raise FileNotFoundError(f"Failed to load Haar Cascade from {cascade_path}")
    return face_cascade

# Fungsi untuk memproses gambar dan mendeteksi wajah
def detect_faces(image_paths, face_cascade, nim):
    faces_data = []
    names_data = []

    for path in image_paths:
        if os.path.splitext(path)[-1].lower() == '.png':
            img = cv2.imread(path, cv2.IMREAD_GRAYSCALE)
            if img is not None:
                faces = face_cascade.detectMultiScale(img, scaleFactor=1.2, minNeighbors=5)
                print(f"Processing {path}: Detected {len(faces)} faces")

                for (x, y, w, h) in faces:
                    face = img[y:y + h, x:x + w]
                    face_resized = cv2.resize(face, (50, 50))
                    faces_data.append(face_resized.flatten())
                    names_data.append(nim)
            else:
                print(f"Failed to read image from {path}")
    return np.asarray(faces_data), names_data

# Fungsi untuk menyimpan data ke file pickle
def save_to_pickle(data, file_path, append=False):
    if append and os.path.exists(file_path):
        with open(file_path, 'rb') as f:
            existing_data = pickle.load(f)
        combined_data = np.append(existing_data, data, axis=0) if isinstance(data, np.ndarray) else existing_data + data
        with open(file_path, 'wb') as f:
            pickle.dump(combined_data, f)
    else:
        with open(file_path, 'wb') as f:
            pickle.dump(data, f)

# Fungsi utama yang menangani seluruh proses
def main():
    if len(sys.argv) <= 2:
        print("No images or NIM received.")
        sys.exit(1)

    json_file_path = sys.argv[1]
    nim = sys.argv[2]
    print(f"Received JSON file path: {json_file_path}")
    print(f"Received NIM: {nim}")

    # Muat daftar path gambar dari file JSON
    try:
        image_paths = load_image_paths(json_file_path)
        print(f"Received {len(image_paths)} images to process")
    except FileNotFoundError as e:
        print(e)
        sys.exit(1)

    # Muat Haar Cascade untuk deteksi wajah
    cascade_path = 'data/haarcascade_frontalface_default.xml'
    try:
        face_cascade = load_face_cascade(cascade_path)
    except FileNotFoundError as e:
        print(e)
        sys.exit(1)

    # Proses deteksi wajah pada gambar-gambar yang diambil
    faces_data, names_data = detect_faces(image_paths, face_cascade, nim)

    if len(faces_data) == 0:
        print("No faces detected.")
        sys.exit(1)

    # Tentukan nama file berdasarkan NIM
    faces_file_path = f"data/../../storage/app/faces/{nim}_faces.pkl"
    names_file_path = f"data/../../storage/app/names/{nim}_names.pkl"

    # Simpan data wajah dan nama ke file pickle
    try:
        save_to_pickle(faces_data, faces_file_path, append=True)
        save_to_pickle(names_data, names_file_path, append=True)
    except Exception as e:
        print(f"Error saving pickle files: {e}")
        sys.exit(1)

    # Sukses
    print(f"Converted {len(faces_data)} faces and saved names {len(names_data)} times.")

if __name__ == '__main__':
    main()

# import sys
# import json
# import numpy as np
# import cv2
# import os
# import pickle

# # Check JSON Argument From PHP
# if len(sys.argv) > 2:
#     json_file_path = sys.argv[1]
#     name = sys.argv[2]
#     print(f"Received JSON file path: {json_file_path}")
#     print(f"Received Name: {name}")

#     with open(json_file_path, 'r') as json_file:
#         image_paths = json.load(json_file)

#     print(f"Received {len(image_paths)} images to process")
# else:
#     print("No images or name received")
#     exit(1)

# # Define dan Check the Haar Cascade Algorithm
# cascade_path = 'data/haarcascade_frontalface_default.xml'
# face_cascade = cv2.CascadeClassifier(cascade_path)

# if face_cascade.empty():
#     print(f"Failed to load cascade classifier at {cascade_path}")
#     exit(1)

# #Start the Face Recognition Process
# faces_data = []
# names_data = []

# for path in image_paths:
#     if os.path.splitext(path)[-1].lower() == '.png':
#         img = cv2.imread(path, cv2.IMREAD_GRAYSCALE)

#         if img is not None:
#             faces = face_cascade.detectMultiScale(img, scaleFactor=1.2, minNeighbors=5) # Scale Factor From 1.2 - 1.3
#             print(f"Processing {path}: Detected {len(faces)} faces")

#             for (x, y, w, h) in faces:
#                 face = img[y:y + h, x:x + w]
#                 face_resized = cv2.resize(face, (50, 50))
#                 faces_data.append(face_resized.flatten())
#                 names_data.append(name)

# # Dump the Faces Data to Pickle File
# faces_data = np.asarray(faces_data)
# faces_file_path = "data/faces.pkl"

# if not os.path.exists(faces_file_path):
#     with open(faces_file_path, 'wb') as f:
#         pickle.dump(faces_data, f)

# else:
#     with open(faces_file_path, 'rb') as f:
#         existing_faces = pickle.load(f)
#     combined_faces = np.append(existing_faces, faces_data, axis=0)

#     with open(faces_file_path, 'wb') as f:
#         pickle.dump(combined_faces, f)

# # Dump the Names Data to Pickle File
# names_file_path = 'data/names.pkl'
# if not os.path.exists(names_file_path):
#     with open(names_file_path, 'wb') as f:
#         pickle.dump(names_data, f)

# else:
#     with open(names_file_path, 'rb') as f:
#         existing_names = pickle.load(f)
#     combined_names = existing_names + names_data

#     with open(names_file_path, 'wb') as f:
#         pickle.dump(combined_names, f)

# # Conclusion For The Process
# print(f"Converted {len(faces_data)} faces and saved names {len(names_data)} times.")
