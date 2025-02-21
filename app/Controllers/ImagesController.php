<?php


namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\ImageProcessing;
use app\core\Locale;
use app\core\Noticer;
use app\core\Tech;
use app\core\View;
use app\models\Pages;
use app\models\Users;
use app\Repositories\PageRepository;

class ImagesController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        View::$route['vars']['styles'][] = 'images';
        return true;
    }
    public function indexAction()
    {
        $gDrive = new GoogleDrive();
        $title = 'Images';
        $files = $gDrive->listFiles();

        $scripts = [
            // 'plugins/ckeditor.js',
            // 'forms-admin-funcs.js',
            'images.js',
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], compact('title', 'files', 'scripts'));
        return View::render();
    }
    public function addAction()
    {
        $filename = preg_replace('/([^a-zа-яєіїґ.,;0-9_-]+)/ui', '', trim($_POST['filename']));

        if (ImageProcessing::saveBase64Image($_POST['image'], $filename) === false) return View::notice('Fail!');


        $gDrive = new GoogleDrive();
        $fileId = $gDrive->create($_SERVER['DOCUMENT_ROOT'] . FILE_MAINGALL . $filename);

        $file = [
            'id' => $fileId,
            'realLink' => $gDrive->getLink($fileId),
            'name' => $filename,
        ];
        $path = '/components/list/image/item';
        View::$route['vars'] = array_merge(View::$route['vars'], compact('file', 'path'));
        View::html();
    }
    public function deleteAction()
    {
        $imageId = $_POST['imageId'];

        if (empty($imageId))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        $gDrive = new GoogleDrive();
        $retult = $gDrive->delete($imageId);

        return $retult ?
            View::notice(['message' => 'Success!', 'location' => 'reload']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
}
