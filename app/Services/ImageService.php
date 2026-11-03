<?php

namespace app\Services;

use app\core\GoogleDrive;
use app\core\ImageProcessing;
use app\core\Locale;
use app\core\Tech;

class ImageService
{
    public static function getImagesList(string $pageToken = '', &$files = [], &$nextPageToken = '', string $folder = 'root'): bool
    {
        $gDrive = new GoogleDrive();
        $_files = $gDrive->listFiles($pageToken, $nextPageToken, $folder === 'root' ? $folder : $gDrive->getFolderId($folder));
        $files = [];
        foreach ($_files as $file) {
            $files[] = [
                'id' => $file['id'],
                'thumbnailLink' => $file['thumbnailLink'],
                'size' => ceil($file['size'] / 1024),
                'realLink' => $gDrive->getLink($file['id']),
                'name' => $file['name'],
                'resol' => empty($file['imageMediaMetadata']) ? '' : $file['imageMediaMetadata']['width'] . 'x' . $file['imageMediaMetadata']['height'],
            ];
        }
        return !empty($files);
    }
    public static function addBase64Image(GoogleDrive $gDrive, string $folder = 'root', string $filename = '', string $b64 = ''): ?array
    {
        $filename = trim($filename);
        $b64 = trim($b64);

        if (empty($filename) || empty($b64)) return null;

        if ($folder === 'gallery')
            ImageProcessing::$maxWidth = 1080;

        $symbols = Locale::$cyrillicPattern;
        $filename = preg_replace("/([^a-z$symbols.,;0-9_-]+)/ui", '', $filename);

        if (ImageProcessing::saveBase64Image($b64, $filename) === false)
            return null;

        $filePath = $_SERVER['DOCUMENT_ROOT'] . FILE_MAINGALL . $filename;

        $fileId = $gDrive->create($filePath, $folder);
        $size = filesize($filePath);
        unlink($filePath);

        $file = [
            'id' => $fileId,
            'realLink' => $gDrive->getLink($fileId),
            'thumbnailLink' => $gDrive->getLink($fileId),
            'name' => $filename,
            'size' => $size,
        ];

        return $file;
    }
}
