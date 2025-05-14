<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait CommonTrait
{
    public function sendResponse($result, $message = 'success', $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    public function sendError($error, $errorMessages = [], $code = 200)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function failsValidate($errors)
    {
        $firstError = $errors->first();

        $response = [
            'success' => false,
            'message' => $firstError,
        ];

        return response()->json($response, 200);
    }


    public function uploadFile($file, $folder_name)
    {
        $folder_name = $folder_name;

        $file_name = rand(1000, 9999) .
            time() .
            '.' .
            $file->getClientOriginalExtension();

        $path = $file->storeAs('public/' . $folder_name, $file_name);
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();

        return [
            'path' => 'storage/' . $path,
            'extension' => $extension,
            'size' => $size
        ];
    }

    public function removeFile($path)
    {
        $filePath = str_replace('storage/public/', 'public/', $path);

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            Log::info("File $filePath deleted successfully.");
        } else {
            Log::warning("File $filePath does not exist.");
        }
    }
}
