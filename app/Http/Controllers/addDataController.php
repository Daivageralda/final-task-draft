<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Validator;
// use Symfony\Component\Process\Process;
// use Symfony\Component\Process\Exception\ProcessFailedException;

// class AddDataController extends Controller
// {
//     public function store(Request $request)
//     {
//         // Validasi input yang ketat
//         $validator = Validator::make($request->all(), [
//             'images' => 'required|array|min:1',
//             'images.*' => 'required|string|regex:/^data:image\/png;base64,/',
//             'nim' => 'required|string|alpha_num|max:10',
//         ], [
//             'images.required' => 'Please capture images',
//             'images.*.regex' => 'Each image must be a valid base64 PNG image.',
//             'nim.required' => 'NIM is required.',
//             'nim.alpha_num' => 'NIM may only contain letters and numbers.',
//             'nim.max' => 'NIM cannot exceed 10 characters.',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['error' => $validator->errors()->first()], 422);
//         }

//         // Tentukan direktori penyimpanan file
//         $folderPath = storage_path('app/uploads/');
//         if (!File::exists($folderPath) && !File::makeDirectory($folderPath, 0755, true)) {
//             return response()->json(['error' => 'Unable to create uploads directory.'], 500);
//         }

//         $imageUrls = [];
//         foreach ($request->images as $img) {
//             try {
//                 $image_parts = explode(";base64,", $img);
//                 $image_base64 = base64_decode($image_parts[1], true);

//                 if ($image_base64 === false) {
//                     return response()->json(['error' => 'Invalid base64 image format.'], 400);
//                 }

//                 $fileName = uniqid() . '.png';
//                 $filePath = $folderPath . $fileName;

//                 File::put($filePath, $image_base64);
//                 $imageUrls[] = $filePath;
//             } catch (\Exception $e) {
//                 Log::error('Error saving image: ' . $e->getMessage());
//                 return response()->json(['error' => 'Failed to save image.'], 500);
//             }
//         }

//         // Menjalankan script Python untuk konversi hanya file .png
//         $pythonPath = escapeshellcmd('C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe');
//         $scriptPath = escapeshellarg(public_path('pythonScripts/add_newData.py'));

//         // Simpan path gambar ke file JSON sementara
//         $jsonFilePath = $folderPath . 'image_paths.json';
//         File::put($jsonFilePath, json_encode($imageUrls, JSON_UNESCAPED_SLASHES));

//         // Gabungkan perintah dengan nim sebagai argumen tambahan
//         $command = [$pythonPath, $scriptPath, $jsonFilePath, $request->nim];

//         // Jalankan perintah menggunakan Process
//         try {
//             $process = new Process($command);
//             $process->run();

//             // Periksa jika eksekusi gagal
//             if (!$process->isSuccessful()) {
//                 throw new ProcessFailedException($process);
//             }

//             $output = $process->getOutput();
//         } catch (\Exception $e) {
//             Log::error('Python script failed: ' . $e->getMessage());
//             return response()->json(['error' => 'Python script execution failed.'], 500);
//         }

//         // Menghapus file JSON sementara
//         if (File::exists($jsonFilePath)) {
//             File::delete($jsonFilePath);
//         }

//         // Menghapus file gambar di folder uploads setelah digunakan
//         foreach (File::files($folderPath) as $file) {
//             File::delete($file->getPathname());
//         }

//         return response()->json([
//             'message' => trim($output),
//         ]);
//     }
// }

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class addDataController extends Controller
{
    public function store(Request $request)
    {
        // $request->validate([
        //     'images' => 'required|array',
        //     'images.*' => 'required|string',
        //     'nim' => 'required|string',
        // ], [
        //     'images.required' => 'Please capture images',
        //     'nim.required' => 'nim is required'
        // ]);
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            // 'images.*' => 'required|string|regex:/^data:image\/png;base64,/',
            'nim' => 'required|string|alpha_num|max:10',
        ], [
            'images.required' => 'Please capture images',
            'images.*.regex' => 'Each image must be a valid base64 PNG image.',
            'nim.required' => 'NIM is required.',
            // 'nim.alpha_num' => 'NIM may only contain letters and numbers.',
            'nim.max' => 'NIM cannot exceed 10 characters.',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // $folderPath = public_path('uploads/');
        // if (!file_exists($folderPath) && !mkdir($folderPath, 0777, true)) {
        //     return response()->json(['error' => 'Unable to create uploads directory.'], 500);
        // }
        // Tentukan direktori penyimpanan file
        $folderPath = storage_path('app/uploads/');
        if (!File::exists($folderPath) && !File::makeDirectory($folderPath, 0755, true)) {
            return response()->json(['error' => 'Unable to create uploads directory.'], 500);
        }

        $facesPath = storage_path('app/faces/');
        if (!File::exists($facesPath) && !File::makeDirectory($facesPath, 0755, true)) {
            return response()->json(['error' => 'Unable to create uploads directory.'], 500);
        }
        $namesPath = storage_path('app/names/');
        if (!File::exists($namesPath) && !File::makeDirectory($namesPath, 0755, true)) {
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

        // Menjalankan script python untuk konversi hanya file .png
        // $pythonPath = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe';
        // $scriptPath = public_path('pythonScripts/add_newData.py');
        $pythonPath = escapeshellcmd('C:\Users\ASUS\AppData\Local\Programs\Python\Python310\python.exe');
        $scriptPath = escapeshellarg(public_path('pythonScripts/add_newData.py'));

        // Simpan path gambar ke file JSON sementara
        // $jsonFilePath = $folderPath . 'image_paths.json';
        // file_put_contents($jsonFilePath, json_encode($imageUrls, JSON_UNESCAPED_SLASHES));
        $jsonFilePath = $folderPath . 'image_paths.json';
        File::put($jsonFilePath, json_encode($imageUrls, JSON_UNESCAPED_SLASHES));

        // Gabungkan perintah untuk shell_exec dengan name sebagai argumen tambahan
        // $command = "$pythonPath $scriptPath " . escapeshellarg($jsonFilePath) . ' ' . escapeshellarg($request->nim);
        $command = "$pythonPath $scriptPath " . escapeshellarg($jsonFilePath) . ' ' . escapeshellarg($request->nim);

        // Menjalankan perintah dengan shell_exec
        // $output = shell_exec($command);
        $output = shell_exec($command . ' 2>&1'); // Capture stderr juga

        if ($output === null) {
            Log::error('Python script failed: ' . $output);
            return response()->json(['error' => 'Python script execution failed.'], 500);
        }
        // Log untuk memeriksa output
        Log::info('Shell Exec Output: ' . $output);

        // // Menghapus file JSON sementara
        // if (file_exists($jsonFilePath)) {
        //     unlink($jsonFilePath);
        // }
        // Menghapus file JSON sementara
        if (File::exists($jsonFilePath)) {
            File::delete($jsonFilePath);
        }
        // // Menghapus file gambar di folder uploads
        // $files = File::files($folderPath);
        // foreach ($files as $file) {
        //     File::delete($file->getPathname());
        // }
        // Menghapus file gambar di folder uploads setelah digunakan
        foreach (File::files($folderPath) as $file) {
            File::delete($file->getPathname());
        }

        // if ($output === null) {
        //     return response()->json(['error' => 'Python script failed to run.'], 500);
        // }

        return response()->json([
            'message' => 'Images and name text file uploaded successfully and images converted to numpy array',
        ]);
    }
}

