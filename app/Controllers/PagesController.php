<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\Locale;
use app\core\Tech;
use app\core\Validator;
use app\core\View;
use app\models\Pages;
use app\models\Users;
use app\Repositories\PageRepository;

class PagesController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        return true;
    }
    public function homeAction()
    {
        self::$route['vars'] = ['slug' => 'home'];
        return $this->showAction();
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        $page = PageRepository::getPage($slug);

        if (empty($page))
            return View::errorCode(404, ['message' => "Page $slug isn’t found!"]);

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
        exit();
    }
    public function editAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            try {
                Pages::edit($_POST, $slug);
            } catch (\Throwable $error){
                return View::notice(['error' => 1, 'message' => $error->getMessage(), 'time' => 3000]);
            }
            // return View::notice(['message' => 'Changes saved successfully!', 'location' => 'reload']);
            return View::notice(['message' => 'Changes saved successfully!']);
            
        }
        $page = Pages::getBySlug($slug);

        if (empty($page)) $page = Pages::$default;

        $page['description'] = '<p>' . str_replace("\n", '</p><p>', $page['description']) . '</p>';

        $page['keywords'] = '';
        if (!empty($page['data']['keywords'])) {
            $page['keywords'] = is_array($page['data']['keywords'])
                ? implode(', ', $page['data']['keywords'])
                : $page['keywords'] = $page['data']['keywords'];
        }
        $page['image_link'] = '';
        if (!empty($page['data']['logo'])) {
            $page['image_link'] = GoogleDrive::getLink($page['data']['logo']);
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
            'styles' => [
                'forms',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
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
    public function addBlockAction()
    {
        View::$route['vars']['block'] = [];
        View::$route['vars']['path'] = 'components/blocks/forms/text';
        return View::html();
    }
    public function setBlockTypeAction()
    {
        $blockType = Validator::validate('blocks', $_POST['blockType'] ?? '');

        if (!$blockType)
            return View::notice(['type' => 'error', 'message' => 'Fail']);
        
        $block = [
            'html' => '',
            'image' => '',
            'type' => $blockType,
        ];
        if (strpos($blockType, '-')){
            $block['direction'] = '';
            $block['order'] = '';
            if ($blockType === 'image-text'){
                $blockType= 'text-image';
                $block['order'] = 'reverse';
            }
        }
        View::$route['vars']['block'] = $block;
        View::$route['vars']['path'] = 'components/blocks/forms/'.$blockType;
        return View::html();
    }
    public function addAction()
    {
        if (!empty($_POST)) {
            $array = $_POST;
            Pages::create($array);
            return View::notice(['message' => 'Changes saved successfully!']);
        }
        $vars = [
            'title' => 'Add page form',
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            'styles' => [
                'forms',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
