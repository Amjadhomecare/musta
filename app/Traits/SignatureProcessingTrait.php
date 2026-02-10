<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

trait SignatureProcessingTrait
{
    /**
     * Process an uploaded signature image to remove background
     * Uses ImageMagick via Symfony Process for best quality and security.
     *
     * @param string $imagePath Path to the uploaded image file
     * @return string Processed image as PNG binary data
     */
    private function processSignatureRemoveBackground(string $imagePath): string
    {
        try {
            $tempOutput = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
            
            // 1. First attempt: Simple fuzz + transparency + trim + padding
            // Use -auto-orient to respect the phone's original orientation tag
            // blocking any unwanted flipping/rotation by standardizing "up"
            $process = new Process([
                'convert',
                $imagePath,
                '-auto-orient', // Fixes orientation to match original view
                '-fuzz', '70%',
                '-transparent', 'white',
                '-trim',
                '+repage',
                '-bordercolor', 'transparent',
                '-border', '10', // Add 10px padding
                $tempOutput
            ]);
            
            $process->run();
            
            if (!$process->isSuccessful() || !file_exists($tempOutput)) {
                // 2. Second attempt: Aggressive (Grayscale -> Level -> Transparent)
                $process = new Process([
                    'convert',
                    $imagePath,
                    '-auto-orient',
                    '-colorspace', 'gray',
                    '-level', '50%,90%',
                    '-fuzz', '25%',
                    '-transparent', 'white',
                    '-trim',
                    '+repage',
                    '-bordercolor', 'transparent',
                    '-border', '10',
                    $tempOutput
                ]);
                
                $process->run();
                
                if (!$process->isSuccessful()) {
                    Log::error('ImageMagick failed: ' . $process->getErrorOutput());
                    return file_get_contents($imagePath);
                }
            }
            
            if (file_exists($tempOutput)) {
                $content = file_get_contents($tempOutput);
                unlink($tempOutput);
                return $content;
            }
            
            return file_get_contents($imagePath);
            
        } catch (\Throwable $e) {
            Log::error('Signature processing failed: ' . $e->getMessage());
            return file_get_contents($imagePath); // Return original if all else fails
        }
    }
}
