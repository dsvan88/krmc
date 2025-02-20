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
    public function showAction()
    {
        extract(self::$route['vars']);
        $page = PageRepository::getPage($slug);

        if (empty($page))
            return View::errorCode(404, ['message' => "Page $slug isn't found!"]);

        $vars = [
            'mainClass' => 'pages',
            'title' => $page['title'],
            'description' => $page['description'],
            'page' => $page,
            'texts' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
            ],
            'og' => PageRepository::formPageOG($page),
        ];

        if (Users::checkAccess('manager')) {
            $vars['dashboard']['slug'] = $slug;
            $vars['dashboard']['id'] = $page['id'];
        }

        View::$path = 'pages/show';
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function addAction()
    {
        $filename = preg_replace('/([^a-zа-яєіїґ.,;0-9_-]+)/ui', '', trim($_POST['filename']));

        if (ImageProcessing::saveBase64Image($_POST['image'], $filename) === false) return View::notice('Fail!');

        
        $gDrive = new GoogleDrive();
        $fileId = $gDrive->create($_SERVER['DOCUMENT_ROOT'].FILE_MAINGALL.$filename);

        $file = [
            'id' => $fileId,
            'realLink' => $gDrive->getLink($fileId),
            'name' => $filename,
        ];
        $path = '/components/list/image/item';
        View::$route['vars'] = array_merge(View::$route['vars'], compact('file', 'path'));
        View::html();
    }
    public function editAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            $array = $_POST;
            $result = Pages::edit($array, $slug);
            if ($result === true)
                return View::message('Changes saved successfully!');
            return View::notice(['error' => 1, 'message' => $result, 'time' => 3000]);
        }

        $page = Pages::getBySlug($slug);

        if (empty($page)) $page = Pages::$default;

        $page['description'] = '<p>' . str_replace("\n", '</p><p>', $page['description']) . '</p>';

        $page['keywords'] = '';
        if (!empty($page['data'])) {
            if (!empty($page['data']['keywords'])) {
                if (is_array($page['data']['keywords']))
                    $page['keywords'] = implode(', ', $page['data']['keywords']);
                else
                    $page['keywords'] = $page['data']['keywords'];
            }
        }
        $page['published_at'] = strtotime($page['published_at']);
        $page['published_at'] = date('Y-m-d', $page['published_at']) . 'T' . date('H:i', $page['published_at']);

        if (!empty($page['expired_at'])) {
            $page['expired_at'] = strtotime($page['expired_at']);
            $page['expired_at'] = date('Y-m-d', $page['expired_at']) . 'T' . date('H:i', $page['expired_at']);
        }
        $vars = [
            'title' => Locale::phrase('Page edit form'),
            'texts' => [
                'SubmitLabel' => 'Save'
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
            'page' => $page,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);

        if (empty($pageId) || !is_numeric($pageId))
            return View::redirect('/');

        Pages::remove($pageId);
        return View::redirect('/');
    }
    // public function addAction()
    // {
    //     if (!empty($_POST)) {
    //         $array = $_POST;
    //         // Pages::create($array);
    //         return View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
    //     }
    //     $vars = [
    //         'title' => 'Add page form',
    //         'texts' => [
    //             'SubmitLabel' => 'Create'
    //         ],
    //         'scripts' => [
    //             // 'plugins/ckeditor.js',
    //             // 'forms-admin-funcs.js',
    //         ],
    //     ];
    //     View::$route['vars'] = array_merge(View::$route['vars'], $vars);

    //     return View::render();
    // }
}
