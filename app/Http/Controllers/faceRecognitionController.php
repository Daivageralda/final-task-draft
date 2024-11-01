<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validation;

class faceRecognitionController extends Controller
{
    public function capture(Request $request)
{
    // $request->validate([
    //         'images' => 'required|array',
    //         'images.*' => 'required|string',
    //     ], [
    //         'images.required' => 'Please capture images',
    //     ]);
            // Validasi input yang ketat
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            'images.*' => 'required|string|regex:/^data:image\/png;base64,/',
            // 'id' => 'required|string|alpha_dash|max:10',
        ], [
            'images.required' => 'Please capture images',
            'images.*.regex' => 'Each image must be a valid base64 PNG image.',
            // 'id.required' => 'Id is required.',
            // 'id.alpha_dash' => 'Name may only contain letters, numbers, dashes and underscores.',
            // id.max' => 'NIM may not be longer than 10 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }



        // $folderPath = public_path('uploads/');
        // if (!file_exists($folderPath) && !mkdir($folderPath, 0777, true)) {
        //     return response()->json(['error' => 'Unable to create uploads directory.'], 500);
        // }
        $folderPath = storage_path('app/uploads/');
        if (!File::exists($folderPath) && !File::makeDirectory($folderPath, 0755, true)) {
            return response()->json(['error' => 'Unable to create uploads directory.'], 500);
        }

        // $imageUrls = [];
        // foreach ($request->images as $img) {
        //     $image_parts = explode(";base64,", $img);
        //     $image_base64 = base64_decode($image_parts[1]);
        //     $fileName = uniqid() . '.png';
        //     $filePath = $folderPath . $fileName;

        //     file_put_contents($filePath, $image_base64);
        //     $imageUrls[] = $filePath;
        // }

        $imageUrls = [];
        foreach ($request->images as $img) {
            try {
                $image_parts = explode(";base64,", $img);
                $image_base64 = base64_decode($image_parts[1], true);

                if ($image_base64 === false) {
                    return response()->json(['error' => 'Invalid base64 image format.'], 400);
                }

                $fileName = uniqid() . '.png';
                $filePath = $folderPath . $fileName;

                File::put($filePath, $image_base64);
                $imageUrls[] = $filePath;
            } catch (\Exception $e) {
                Log::error('Error saving image: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to save image.'], 500);
            }
        }

        // // Menjalankan script python untuk konversi hanya file .png
        // $pythonPath = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe';
        // $scriptPath = public_path('pythonScripts/presence.py');

        // // Simpan path gambar ke file JSON sementara
        // $jsonFilePath = $folderPath . 'image_paths.json';
        // file_put_contents($jsonFilePath, json_encode($imageUrls, JSON_UNESCAPED_SLASHES));

        // // Gabungkan perintah untuk shell_exec dengan name sebagai argumen tambahan
        // $command = "$pythonPath $scriptPath " . escapeshellarg($jsonFilePath) . ' ' . escapeshellarg($request->name);

        // // Menjalankan perintah dengan shell_exec
        // $output = shell_exec($command);
                // Menjalankan script Python untuk konversi hanya file .png
        $pythonPath = escapeshellcmd('C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe');
        $scriptPath = escapeshellarg(public_path('pythonScripts/presence.py'));

        // Simpan path gambar ke file JSON sementara
        $jsonFilePath = $folderPath . 'image_paths.json';
        File::put($jsonFilePath, json_encode($imageUrls, JSON_UNESCAPED_SLASHES));

        // Gabungkan perintah untuk shell_exec dengan id (NIM) sebagai argumen tambahan
        $command = "$pythonPath $scriptPath " . escapeshellarg($jsonFilePath) . ' ' . escapeshellarg($request->id);

        // Menjalankan perintah dengan shell_exec dan memeriksa error
        $output = shell_exec($command . ' 2>&1'); // Capture stderr

        // Log untuk memeriksa output
        // Log::info('Shell Exec Output: ' . $output);
        if ($output === null) {
            Log::error('Python script failed: ' . $output);
            return response()->json(['error' => 'Python script execution failed.'], 500);
        }
        // Log untuk memeriksa output dari script Python
        Log::info('Shell Exec Output: ' . $output);

        // Menghapus file JSON sementara
        // if (file_exists($jsonFilePath)) {
        //     unlink($jsonFilePath);
        // }
        if (File::exists($jsonFilePath)) {
            File::delete($jsonFilePath);
        }

        // Menghapus file gambar di folder uploads
        // $files = File::files($folderPath);
        // foreach ($files as $file) {
        //     File::delete($file->getPathname());
        // }
        foreach (File::files($folderPath) as $file) {
            File::delete($file->getPathname());
        }

        return response()->json([
            'message' => trim($output), // Memastikan output bersih
        ]);
        // if ($output === null) {
        //     return response()->json(['error' => 'Python script failed to run.'], 500);
        // }

        // return response()->json([
        //     'message' => $output,
        // ]);
}
}
