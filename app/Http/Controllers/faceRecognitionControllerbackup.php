<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class faceRecognitionController extends Controller
{
    public function capture(Request $request)
    {
        // Validasi input gambar
        $request->validate([
            'image' => 'required|string',
        ], [
            'image.required' => 'Image data is required',
        ]);

        // Decode base64 image
        $imageData = $request->input('image');
        list($type, $imageData) = explode(';', $imageData);
        list(, $imageData)      = explode(',', $imageData);
        $imageData = base64_decode($imageData);

        // Tentukan folder sementara dan nama file
        $folderPath = public_path('temp/');
        if (!file_exists($folderPath) && !mkdir($folderPath, 0777, true)) {
            return response()->json(['error' => 'Unable to create temporary directory.'], 500);
        }

        $fileName = uniqid() . '.jpg';
        $filePath = $folderPath . $fileName;
        file_put_contents($filePath, $imageData);

        // Jalankan Python script untuk mendeteksi wajah
        $pythonPath = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe';
        $scriptPath = public_path('pythonScripts/presence.py');

        // Jalankan perintah Python dengan shell_exec
        $command = "$pythonPath $scriptPath " . escapeshellarg($filePath);
        $output = shell_exec($command);
        // $output = trim($output);

        // Log output dari shell_exec
        Log::info('Face Recognition Output: ' . $output);

        // Hapus file sementara
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Cek apakah output berhasil dan kembalikan nama atau pesan kesalahan
        if ($output === null) {
            return response()->json(['error' => 'Face recognition script failed to run.'], 500);
        }

        return response()->json([
            'status' => 'success',
            'name' => $output
        ]);
    }
}
