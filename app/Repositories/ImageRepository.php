<?php

namespace app\Repositories;

use app\core\GoogleDrive;
use app\core\Tech;

class ImageRepository
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
        // array_map(
        //     fn($e) => [
        //         'id' => $e['id'],
        //         'thumbnailLink' => $e['thumbnailLink'],
        //         'size' => ceil($e['size'] / 1024),
        //         'realLink' => $gDrive->getLink($e['id']),
        //         'name' => $e['name'],
        //         'resol' => empty($e['imageMediaMetadata']) ? '' : $e['imageMediaMetadata']['width'] . 'x' . $e['imageMediaMetadata']['height'],
        //     ],
        //     $_files
        // );
        return !empty($files);
    }
}
