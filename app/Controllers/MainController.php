<?php


namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\View;
use app\models\Settings;
use app\models\Users;
use app\Repositories\ImageRepository;

class MainController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        return true;
    }
    public function galleryAction()
    {

        $gDrive = new GoogleDrive;
        $gDrive::$maxPerPage = 40;
        $gallery = $gDrive->listFiles('', $nextPageToken, $gDrive->getFolderId('gallery'));

        $vars = [
            'title' => 'Gallery',
            'gallery' => $gallery,
            'nextPageToken' => $nextPageToken,
            'page' => [
                'title' => 'Gallery',
                'subtitle' => 'Peek on us!:)',
            ],
            'texts' => [
                'SubmitLabel' => 'Create',
                'edit' => 'Edit',
            ],
            'styles' => [
                'gallery',
            ],
            'scripts' => [
                'images.js',
            ],
        ];

        if (Users::checkAccess('manager')) {
            $vars['dashboard']['slug'] = 'main/gallery';
            $vars['dashboard']['id'] = 'edit';
        }

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function galleryEditAction()
    {
        $pageToken = '';
        extract(self::$route['vars']);

        if (!empty($_POST['pageToken']))
            $pageToken = $_POST['pageToken'];

        ImageRepository::getImagesList($pageToken, $gallery, $nextPageToken, 'gallery');

        $vars = [
            'title' => 'Gallery',
            'gallery' => $gallery,
            'nextPageToken' => $nextPageToken,
            'page' => [
                'title' => 'Gallery',
                'subtitle' => 'Peek on us!:)',
            ],
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            'styles' => [
                'images',
            ],
            'scripts' => [
                'images.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function contactsAction()
    {

        $contacts = Settings::load('contacts');
        $socials = Settings::load('socials');

        $vars = [
            'title' => 'Contacts',
            'contacts' => $contacts,
            'socials' => $socials,
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            'styles' => [
                'contacts',
            ],
            // 'scripts' => [
            //     'plugins/ckeditor.js',
            //     'forms-admin-funcs.js',
            //     'images-pad.js',
            // ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
