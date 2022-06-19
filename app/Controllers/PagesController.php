<?php


namespace app\Controllers;

use Transliterator;
use app\core\Controller;
use app\core\Pages;
use app\core\View;
use app\models\Settings;
use app\core\Locale;

class PagesController extends Controller
{
    public function indexAction()
    {
        $index = Settings::getPage('index');
        if (!$index) {
            View::render('{{ Main_Home_Page_Title }}', []);
            exit();
        }
        self::$route['vars'] = ['shortName' => 'index'];
        $this->showAction();
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        $settings = Settings::getPage($shortName);
        if (!$settings) {
            View::errorCode(404, ['message' => "Page $shortName isn't found!"]);
        }
        if (PAGES_AS_LOCAL_FILE) {
            self::$route['action'] = $shortName;
            View::set(self::$route);
            View::render('{{ Main_Home_Page_Title }}', []);
            exit();
        }
        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/edit/{$settings['id']}' title='Редагувати'><i class='fa fa-pencil-square-o'></i></a>";
            if ($shortName !== 'index') {
                $dashboard .= "<a href='/page/delete/{$settings['id']}' onclick='return confirm(\"Are you sure?\")' title='Видалити'><i class='fa fa-trash-o'></i></a>";
            }
            $dashboard .= "</span>";
        }
        $vars = [
            'html' => $settings['value'],
            'dashboard' => $dashboard,
        ];
        View::renderPage($settings['name'], $vars);
        exit();
    }
    public function editAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            $array = $_POST;

            if ($array['short_name'] !== 'index') {
                $name = Locale::translitization($array['title']);
            } else {
                $name = 'index';
            }
            Pages::save($name, $array);
            View::message(['error' => 0, 'message' => '{{ Changes_Save_Success }}']);
        }

        $settings = Settings::getPageById($pageId);
        $pageData['id'] = $settings['id'];
        $pageData['short_name'] = $settings['short_name'];
        $pageData['title'] = $settings['name'];
        preg_match("/<h3 class='index-subtitle'>([^<]*)<\/h3>/", $settings['value'], $match);
        $pageData['subtitle'] = $match[1];
        $preDiv = '<div class=\'index-text\'>';
        $htmlStartPos = mb_strpos($settings['value'], $preDiv, 0, 'UTF-8') + mb_strlen($preDiv, 'UTF-8');
        $htmlEndPos = mb_strrpos($settings['value'], '</div>', 0, 'UTF-8') - mb_strlen($settings['value'], 'UTF-8');
        $pageData['html'] = trim(mb_substr($settings['value'], $htmlStartPos, $htmlEndPos, 'UTF-8'));
        $vars = [
            'texts' => [
                'pageAddBlockTitle' => '{{ Page_Add_Block_Title }}',
                'pageAddSubmitTitle' => '{{ Page_Add_Block_Submit_Title }}'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
            'pageData' => $pageData,
        ];
        View::render('{{ Page_Add_Page_Title }}', $vars);
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

            $name = Locale::translitization($array['title']);
            Pages::save($name, $array);
            View::message(['error' => 0, 'message' => '{{ Changes_Save_Success }}']);
        }
        $vars = [
            'texts' => [
                'pageAddBlockTitle' => '{{ Page_Add_Block_Title }}',
                'pageAddSubmitTitle' => '{{ Page_Add_Block_Submit_Title }}'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];

        View::render('{{ Page_Add_Page_Title }}', $vars);
    }
}
