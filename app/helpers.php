<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

if (!function_exists('uploadToCloud')) {
    function uploadToCloud($filePath)
    {
        $result = Cloudinary::upload($filePath);

        // Log or return response if needed
        Log::info('Cloudinary upload result: ' . json_encode($result));

        return $result;
    }
}

if (!function_exists('deleteCloud')) {
    function deleteCloud($imageUrl)
    {
        $parsedUrl = parse_url($imageUrl);
        $path = $parsedUrl['path'] ?? '';

        $pathParts = explode('/', $path);
        $filenameWithExt = end($pathParts);

        // Remove the extension
        $publicId = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        // Delete from Cloudinary
        $result = Cloudinary::uploadApi()->destroy($publicId);

        // Log or return response if needed
        Log::info('Cloudinary delete result: ' . json_encode($result));

        return $result;
    }
}
