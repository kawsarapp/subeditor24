<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Store and optimize an uploaded image into WebP format
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param int $quality (1-100)
     * @param int $maxWidth
     * @return string Public Asset URL
     */
    public function optimizeAndStore(UploadedFile $file, string $folder = 'news-uploads', int $quality = 82, int $maxWidth = 1200): string
    {
        $filename = Str::random(24) . '.webp';
        $relativeDir = 'public/' . trim($folder, '/');
        $fullDirPath = storage_path('app/' . $relativeDir);

        if (!file_exists($fullDirPath)) {
            mkdir($fullDirPath, 0755, true);
        }

        $destinationPath = $fullDirPath . '/' . $filename;
        $imagePath = $file->getRealPath();

        $success = false;

        // Try GD conversion to WebP with auto-resizing
        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            try {
                $fileContent = file_get_contents($imagePath);
                $sourceImage = @imagecreatefromstring($fileContent);

                if ($sourceImage !== false) {
                    $origWidth = imagesx($sourceImage);
                    $origHeight = imagesy($sourceImage);

                    // Resize if width exceeds max
                    if ($origWidth > $maxWidth) {
                        $newWidth = $maxWidth;
                        $newHeight = (int)round(($origHeight * $maxWidth) / $origWidth);

                        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                        imagealphablending($resizedImage, false);
                        imagesavealpha($resizedImage, true);

                        imagecopyresampled(
                            $resizedImage, $sourceImage,
                            0, 0, 0, 0,
                            $newWidth, $newHeight,
                            $origWidth, $origHeight
                        );

                        imagewebp($resizedImage, $destinationPath, $quality);
                        imagedestroy($resizedImage);
                    } else {
                        imagealphablending($sourceImage, false);
                        imagesavealpha($sourceImage, true);
                        imagewebp($sourceImage, $destinationPath, $quality);
                    }

                    imagedestroy($sourceImage);
                    $success = true;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("WebP optimization failed, falling back to standard store: " . $e->getMessage());
                $success = false;
            }
        }

        // Fallback to standard Laravel storage if GD failed
        if (!$success || !file_exists($destinationPath)) {
            $storedPath = $file->store($folder, 'public');
            return asset('storage/' . $storedPath);
        }

        return asset('storage/' . trim($folder, '/') . '/' . $filename);
    }
}
