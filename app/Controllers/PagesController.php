<?php


namespace app\Controllers;

use Transliterator;
use app\core\Controller;
use app\core\View;
use app\models\Settings;
use app\core\Locale;
use app\models\Pages;

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
        $page = Pages::findBy('slug', $slug);
        if ($page){
            $page = $page[0];
        } else {
            if ( $slug === 'home' ){
                $page = [
                    'id' => 'home',
                    'title' => '&ltNo Data&gt;',
                    'subtitle' => '&ltNo Data&gt;',
                    'html' => '&ltNo Data&gt;',
                ];
            }
            else 
                View::errorCode(404, ['message' => "Page $slug isn't found!"]);
        }

        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/edit/{$page['id']}' title='Редагувати' class='fa fa-pencil-square-o'></a>";
            if ($slug !== 'home') {
                $dashboard .= "<a href='/page/delete/{$page['id']}' onclick='return confirm(\"Are you sure?\")' title='Видалити' class='fa fa-trash-o'></a>";
            }
            $dashboard .= '</span>';
        }
        $vars = [
            'title' => $page['title'],
            'texts' => [
                'title' => trim($page['title']),
                'subtitle' => trim($page['subtitle']),
                'html' => trim($page['html']),
            ],
            'dashboard' => $dashboard,
        ];
        View::renderPage($vars);
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

        if (is_numeric($pageId)){
            $page = Pages::find($pageId);
        }
        else {
            $dummyPage = [
                'title'=>$pageId,
                'type'=>'game',
                'subtitle'=>'',
                'html'=>'',
            ];
            $pageId = Pages::create($dummyPage);
            View::redirect("/page/edit/$pageId");
        }

        $vars = [
            'title' => 'Page edit form',
            'texts' => [
                'SubmitLabel' => 'Save'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
            'page' => $page,
        ];
        View::render($vars);
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);
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
        View::render($vars);
    }
}
