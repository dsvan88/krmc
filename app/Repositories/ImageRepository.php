<?

namespace app\Repositories;

use app\core\GoogleDrive;

class ImageRepository
{
    public static function getImagesList(string $pageToken = '', &$files=[], &$nextPageToken=''): void
    {
        $gDrive = new GoogleDrive();
        $files = $gDrive->listFiles($pageToken);
        $nextPageToken = $_SESSION['nextPageToken'];
    }
}