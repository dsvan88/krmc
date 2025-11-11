<?php


namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\View;
use app\models\Settings;

class MainController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        return true;
    }
    public function galleryAction()
    {

        $images = Settings::load('img');
        $gallery = array_map(fn($img) => GoogleDrive::getLink($img), $images['background']['value']);

        $vars = [
            'title' => 'Gallery',
            'gallery' => $gallery,
            'page' => [
                'title' => 'Gallery',
            ],
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            'styles' => [
                'gallery',
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
    public function contactsAction()
    {

        $contacts = Settings::load('contacts');

        $vars = [
            'title' => 'Contacts',
            'contacts' => $contacts,
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            // 'styles' => [
            //     'forms',
            // ],
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
