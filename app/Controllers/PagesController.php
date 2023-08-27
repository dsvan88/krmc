<?php


namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Pages;
use app\Repositories\PageRepository;

class PagesController extends Controller
{
    public function homeAction()
    {
        self::$route['vars'] = ['slug' => 'home'];
        $this->showAction();
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        $page = PageRepository::getPage($slug);

        if (empty($page))
            View::errorCode(404, ['message' => "Page $slug isn't found!"]);

        $vars = [
            'mainClass' => 'pages',
            'title' => $page['title'],
            'page' => $page,
            'texts' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
            ]
        ];
        if (!empty($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $vars['dashboard'] = (empty($page['id']) ? $game : $page['id']);
        }

        View::$path = 'pages/show';

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
        exit();
    }
    public function editAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            $array = $_POST;
            Pages::edit($array, $pageId);
            View::message('Changes saved successfully!');
        }

        if (is_numeric($pageId)) {
            $page = Pages::find($pageId);
            $page['description'] = '<p>'.str_replace("\n", '</p><p>', $page['description']).'</p>';

            $page['keywords'] = '';
            if (!empty($page['data'])) {
                $page['data'] = json_decode($page['data'], true);
                if (isset($page['data']['keywords']) && !empty($page['data']['keywords'])) {
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
        } else {
            $dummyPage = [
                'title' => $pageId,
                'type' => 'game',
                'subtitle' => '',
                'description' => '',
                'html' => '',
                'published_at' => $_SERVER['REQUEST_TIME'],
                'expired_at' => '',
            ];
            $pageId = Pages::create($dummyPage);
            View::redirect("/page/edit/$pageId");
        }

        $vars = [
            'title' => Locale::phrase('Page edit form'),
            'texts' => [
                'SubmitLabel' => 'Save'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
            'page' => $page,
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);

        if (empty($pageId) || !is_numeric($pageId))
            return View::redirect('/');

        Pages::remove($pageId);
        View::redirect('/');
    }
    public function addAction()
    {
        if (!empty($_POST)) {
            $array = $_POST;
            Pages::create($array);
            View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
        }
        $vars = [
            'title' => 'Add page form',
            'texts' => [
                'SubmitLabel' => 'Create'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
}
