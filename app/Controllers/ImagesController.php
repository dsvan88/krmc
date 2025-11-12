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
use app\models\Settings;
use app\models\Users;
use app\Repositories\ImageRepository;
use app\Repositories\PageRepository;

class ImagesController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        View::$route['vars']['styles'][] = 'images';
        return true;
    }
    public function getMoreAction()
    {
        $pageToken = '';
        extract(self::$route['vars']);

        if (!empty($_POST['pageToken']))
            $pageToken = $_POST['pageToken'];

        $folder = '';
        if (!empty($_POST['type']))
            $folder = preg_replace('/[^a-z0-9_+ -]/ui', '', trim($_POST['type']));

        if (!ImageRepository::getImagesList($pageToken, $files, $nextPageToken, $folder)) {
            return View::notice(['message' => 'Image’s list is empty']);
        }

        $html = '';
        $backgrounds = Settings::getImage('background')['value'];
        $path = $_SERVER['DOCUMENT_ROOT'] . View::$viewsFolder . "/components/list/image/item.php";

        foreach ($files as $file) {
            ob_start();
            require $path;
            $html .= ob_get_clean();
        }
        return View::response(compact('html', 'nextPageToken'));
    }
    public function indexAction()
    {
        $pageToken = '';
        extract(self::$route['vars']);

        if (!empty($_POST['pageToken']))
            $pageToken = $_POST['pageToken'];

        if (!ImageRepository::getImagesList($pageToken, $files, $nextPageToken)) {
            return View::notice(['message' => 'Image’s list is empty']);
        }

        $title = 'Images';
        $backgrounds = Settings::getImage('background')['value'];
        $scripts = [
            'images.js',
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], compact('title', 'files', 'backgrounds', 'scripts', 'nextPageToken'));

        return View::render();
    }
    public function listAction()
    {
        $pageToken = '';
        extract(self::$route['vars']);

        if (!empty($_POST['pageToken']))
            $pageToken = $_POST['pageToken'];

        if (!ImageRepository::getImagesList($pageToken, $files, $nextPageToken)) {
            return View::notice(['message' => 'Image’s list is empty']);
        }

        return View::response(compact('files', 'nextPageToken'));
    }
    public function addAction()
    {
        $symbols = Locale::$cyrillicPattern;
        $filename = preg_replace("/([^a-z$symbols.,;0-9_-]+)/ui", '', trim($_POST['filename']));

        if (ImageProcessing::saveBase64Image($_POST['image'], $filename) === false) return View::notice('Fail!');

        $filePath = $_SERVER['DOCUMENT_ROOT'] . FILE_MAINGALL . $filename;
        $gDrive = new GoogleDrive();

        $folder = '';
        if (!empty($_POST['type']))
            $folder = preg_replace('/[^a-z0-9_+ -]/ui', '', trim($_POST['type']));

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
        if (!empty($_POST['prompt'])) {
            return View::response($file);
        }

        $path = '/components/list/image/item';

        View::$route['vars'] = array_merge(View::$route['vars'], ['file' => $file, 'path' => $path, 'backgrounds' => []]);
        return View::html();
    }
    public function deleteAction()
    {
        $imageId = $_POST['imageId'];

        if (empty($imageId))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        $gDrive = new GoogleDrive();
        $result = $gDrive->delete($imageId);

        return $result ?
            View::notice(['message' => 'Success!', 'location' => 'reload']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    public function backgroundSetAction()
    {
        $imageId = trim($_POST['imageId']);

        if (empty($imageId))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        try {
            $images = Settings::findBy('type', 'img', 1)[0];
            foreach ($images['setting'] as $index => $image) {
                if ($image['slug'] !== 'background') continue;
                $images['setting'][$index]['value'][] = $imageId;
            }

            $result = Settings::edit($images['id'], ['setting' => $images['setting']]);
        } catch (\Throwable $error) {
            Tech::dump($error);
            $result = false;
        }
        return $result ?
            View::notice(['message' => 'Success!']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    public function backgroundRemoveAction()
    {
        $imageId = trim($_POST['imageId']);

        if (empty($imageId))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        try {
            $images = Settings::findBy('type', 'img', 1)[0];
            foreach ($images['setting'] as $index => $image) {
                if ($image['slug'] !== 'background') continue;
                $images['setting'][$index]['value'] = array_filter($images['setting'][$index]['value'], function ($element) use ($imageId) {
                    return $element !== $imageId;
                });
            }

            $result = Settings::edit($images['id'], ['setting' => $images['setting']]);
        } catch (\Throwable $error) {
            Tech::dump($error);
            $result = false;
        }
        return $result ?
            View::notice(['message' => 'Success!']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    public function backgroundGroupAction()
    {
        $imageIds = trim($_POST['file_ids']);

        if (empty($imageIds))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        $imageIds = json_decode($imageIds, true);
        try {
            $images = Settings::findBy('type', 'img', 1)[0];
            foreach ($images['setting'] as $index => $image) {
                if ($image['slug'] !== 'background') continue;
                $images['setting'][$index]['value'] = $imageIds;
            }

            $result = Settings::edit($images['id'], ['setting' => $images['setting']]);
        } catch (\Throwable $error) {
            Tech::dump($error);
            $result = false;
        }
        return $result ?
            View::notice(['message' => 'Success!']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
    public function deleteGroupAction()
    {
        $imageIds = trim($_POST['file_ids']);

        if (empty($imageIds))
            return View::notice(['type' => 'error', 'message' => 'Fail!']);

        $imageIds = json_decode($imageIds, true);
        try {
            $gDrive = new GoogleDrive();
            $count = count($imageIds);
            for ($x = 0; $x < $count; $x++) {
                $result = $gDrive->delete($imageIds[$x]);
            }
        } catch (\Throwable $error) {
            Tech::dump($error);
            $result = false;
        }
        return $result ?
            View::notice(['message' => 'Success!', 'location' => 'reload']) :
            View::notice(['type' => 'error', 'message' => 'Fail!']);
    }
}
