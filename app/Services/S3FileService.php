<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class S3FileService
{


     /**
     * Upload a video to a given S3-compatible disk (e.g. 'r2' or 'beta')
     * using a safe, streamed approach and a structured filename:
     *   branch_maidId_slugName_YYYYmmdd_HHMMSS.ext
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $disk        Disk name from filesystems.php (e.g. 'r2' or 'beta')
     * @param string $directory   Folder inside the bucket (e.g. 'video')
     * @param int|string|null $maidId
     * @param string|null $maidName
     * @param string $branchEnvKey Env key for branch code (default: 'branch')
     * @return string|null         Public URL or null on failure
     */
        public function uploadVideo(
            $file,
            string $disk = 'r2',
            string $directory = 'video',
            $maidId = null,
            ?string $maidName = null,
            string $branchEnvKey = 'branch'
        ): ?string {
            try {
                if (!$file || !$file->isValid()) {
                    Log::warning('uploadVideo: invalid upload file.');
                    return null;
                }

                $branch = env($branchEnvKey, 'branch');

                // Sanitize maidName
                $slugName = \Illuminate\Support\Str::of($maidName ?? 'unknown')
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/i', '_')
                    ->trim('_')
                    ->substr(0, 60);

                $ext = strtolower($file->getClientOriginalExtension() ?: 'dat');
                $base = "{$branch}_" . ($maidId ?? '0') . "_{$slugName}_" . now()->format('Ymd_His');
                $filename = "{$base}.{$ext}";
                $dir = trim($directory, '/');
                $path = "{$dir}/{$filename}";

                $options = [
                    'visibility'   => 'public',
                    'ContentType'  => $file->getMimeType(),
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ];

                // Stream upload (no memory issues for large files)
                $ok = Storage::disk($disk)->putFileAs($dir, $file, $filename, $options);

                if (!$ok) {
                    Log::error("uploadVideo: failed to upload to {$disk} at {$path}");
                    return null;
                }

                return Storage::disk($disk)->url($path);
            } catch (\Throwable $e) {
                Log::error('uploadVideo error: ' . $e->getMessage());
                return null;
            }
        }


    public function uploadToS3($file, $directory, $resize = false, $width = 400, $height = 400)
    {
        try {
            $fileContent = null;

            if ($resize) {
                $image = Image::make($file)
                    ->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 75);

                $fileContent = (string) $image;
            } else {
                $fileContent = file_get_contents($file);
            }

            $fileName = uniqid('maid_') . '_' . $file->getClientOriginalName();
            $filePath = $directory . '/' . $fileName;

            $success = Storage::disk('beta')->put($filePath, $fileContent);

            if (!$success) {
                Log::error("File upload to S3 failed: $filePath");
                return null;
            }

            return Storage::disk('beta')->url($filePath);
        } catch (\Exception $e) {
            Log::error("Error uploading file to S3: " . $e->getMessage());
            return null;
        }
    }

    public function deletePreviousFileFromS3($fileUrl, $disk)
    {
        if ($fileUrl) {
            $normalizedUrl = str_replace("\\", "/", $fileUrl);
            $urlParts = parse_url($normalizedUrl);
            $path = ltrim($urlParts['path'], '/');

            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }


    public function uploadToR2($file, $directory, $resize = false, $width = 400, $height = 400)
    {
        try {
            $fileContent = null;

            if ($resize) {
                $image = Image::make($file)
                    ->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 75);

                $fileContent = (string) $image;
            } else {
                $fileContent = file_get_contents($file);
            }

            $fileName = uniqid('maid_') . '_' . $file->getClientOriginalName();
            $filePath = $directory . '/' . $fileName;

            $success = Storage::disk('r2')->put($filePath, $fileContent);

            if (!$success) {
                Log::error("File upload to R2 failed: $filePath");
                return null;
            }

            return Storage::disk('r2')->url($filePath);
        } catch (\Exception $e) {
            Log::error("Error uploading file to R2: " . $e->getMessage());
            return null;
        }
    }

    public function deletePreviousFileFromR2($fileUrl, $disk)
    {
        if ($fileUrl) {
            $normalizedUrl = str_replace("\\", "/", $fileUrl);
            $urlParts = parse_url($normalizedUrl);
            $path = ltrim($urlParts['path'], '/');

            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }


     /**
     * Upload a file to the specified S3 disk and return the file URL.
     *
     * @param string $disk
     * @param string $folder
     * @param string $fileName
     * @param mixed $fileContent
     * @return string|null
     */
    public function uploadS3File($disk, $folder, $fileName, $fileContent)
    {
        $path = Storage::disk($disk)->put($folder . $fileName, $fileContent);

        if ($path) {
            return Storage::disk($disk)->url($folder . $fileName);
        }

        return null; // Return null if upload fails
    }

    
}
