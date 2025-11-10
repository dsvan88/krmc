<?

namespace app\Repositories;

use app\core\GoogleDrive;

class ImageRepository
{
    public static function getImagesList(string $pageToken = '', &$files = [], &$nextPageToken = ''): bool
    {
        $gDrive = new GoogleDrive();
        $_files = $gDrive->listFiles($pageToken, $nextPageToken);
        $files = array_map(
            fn($e) => [
                'id' => $e['id'],
                'thumbnailLink' => $e['thumbnailLink'],
                'size' => ceil($e['size'] / 1024),
                'realLink' => $gDrive->getLink($e['id']),
                'name' => $e['name'],
                'resol' => $e['imageMediaMetadata']['width'] . 'x' . $e['imageMediaMetadata']['height'],
            ],
            $_files
        );
        return !empty($files);
    }
}
