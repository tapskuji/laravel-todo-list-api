<?php


namespace App\Services;


use Illuminate\Support\Facades\File;

class Base64ToImageService
{
    /**
     * @param $path
     * @param $contents
     * @param $oldImage
     * @return string
     * @throws \Exception
     */
    public function saveToDrive($path, $contents, $oldImage): string
    {
        $dataUrlPrefixForPng = 'data:image/png;base64,';
        $dataUrlPrefixForJpg = 'data:image/jpg;base64,';
        $imageExtension = '';

        if (strpos($contents, $dataUrlPrefixForPng) === 0) {
            $imageExtension = 'png';
            $imageStartPosition = strlen($dataUrlPrefixForPng);
        } elseif (strpos($contents, $dataUrlPrefixForJpg) === 0) {
            $imageExtension = 'jpg';
            $imageStartPosition = strlen($dataUrlPrefixForJpg);
        } else {
            throw new \Exception("Invalid data URL format. Expected {$dataUrlPrefixForPng} or {$dataUrlPrefixForJpg}");
        }

        $image_base64_string = substr($contents, $imageStartPosition);
        $images_name = 'profile-image-'. time() . '.' . $imageExtension;
        $imagePath = $path . $images_name;
        $successfulUpload = File::put($imagePath, $image_base64_string);

        $maxImageSize = (int) env('MAX_FILE_SIZE_IN_BYTES');

        if ($successfulUpload && File::size($imagePath) > $maxImageSize) {
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }

            throw new \Exception("Invalid image size. Max image size is " . env('MAX_FILE_SIZE_IN_MEGABYTES') . "MB");
        }

        // delete old profile picture
        $oldImagePath = $path . $oldImage;
        if (File::exists($oldImagePath)) {
            File::delete($oldImagePath);
        }

        return $images_name;
    }
}
