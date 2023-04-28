<?php

namespace app\Controllers;

use app\core\ImageProcessing;
use app\core\Controller;
use app\core\Locale;
use app\core\Paginator;
use app\core\View;
use app\models\News;
use app\models\Settings;

class NewsController extends Controller
{
    public function showListAction()
    {
        if (isset(self::$route['vars']))
            extract(self::$route['vars']);
        $page = 0;
        if (isset($pageNum))
            $page = (int) $pageNum;

        $newsCount = News::getCount();
        $newsAll = News::getPerPage($page);
        $defaultImage = Settings::getImage('news_default');

        $setDashBoard = false;
        if (isset($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'], true)) {
            $setDashBoard = true;
        }

        $title = '{{ News_Show_List_Page_Title }}';
        $texts = [
            'ReadMore' => '{{ News_Block_Read_More }}'
        ];
        $paginator = Paginator::news(['page' => $page, 'count' => $newsCount]);
        View::render(compact('title', 'texts', 'newsAll', 'newsCount', 'page', 'setDashBoard', 'paginator', 'defaultImage'));
    }
    public function showItemAction()
    {
        extract(self::$route['vars']);
        $newsData = News::findBy('id', $newsId);
        if (!empty($newsData['logo']))
            $newsData['logo'] = ImageProcessing::inputImage(FILE_MAINGALL . 'news/' . $newsData['logo'], ['title' => $newsData['title'], 'class' => 'news__item-logo_image']);
        else
            $newsData['logo'] = ImageProcessing::inputImage(Settings::getImage('news_default')['value'], ['title' => Locale::phrase('{{ News_Change_Logo }}'), 'class' => 'news__item-logo_image']);

        $vars = [
            'title' => '{{ News_Show_Item_Page_Title }}',
            'newsData' => $newsData
        ];
        View::render($vars);
    }
    public function editItemAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            $array = $_POST;
            if (!empty($array['main-image'])) {
                $path = $_SERVER['DOCUMENT_ROOT'] . FILE_MAINGALL . 'news';
                $filename = md5($array['main-image']);
                $image = ImageProcessing::saveBase64Image($array['main-image'], $filename, $path);
                if ($image) {
                    $array['logo'] = $image['filename'];
                }
            }
            News::edit($array, $newsId);
            View::message('Changes saved successfully!');
        }

        $newsData = News::findBy('id',$newsId);
        if (!empty($newsData['logo']))
            $newsData['logo'] = ImageProcessing::inputImage(FILE_MAINGALL . 'news/' . $newsData['logo'], ['title' => $newsData['title'], 'class' => 'news__item-logo_image']);
        else
            $newsData['logo'] = ImageProcessing::inputImage(Settings::getImage('news_default')['value'], ['title' => Locale::phrase('{{ News_Change_Logo }}'), 'class' => 'news__item-logo_image']);

        $vars = [
            'title' => '{{ News_Edit_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Edit_Block_Title }}',
                'SubmitLabel' => '{{ News_Edit_Block_Submit_Title }}'
            ],
            'newsData' => $newsData,
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public function editPromoItemAction()
    {
        if (!empty($_POST)) {
            News::edit($_POST, 'promo');
            View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
        }
        $newsData = News::getPromo();
        $vars = [
            'title' => '{{ News_Edit_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Edit_Block_Title }}',
                'SubmitLabel' => '{{ News_Edit_Block_Submit_Title }}'
            ],
            'newsData' => $newsData,
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];

        View::render($vars);
    }
    public function addItemAction()
    {
        if (!empty($_POST)) {
            $array = $_POST;
            if (!empty($array['main-image'])) {
                $path = $_SERVER['DOCUMENT_ROOT'] . FILE_MAINGALL . 'news';
                $filename = md5($array['main-image']);
                $image = ImageProcessing::saveBase64Image($array['main-image'], $filename, $path);
                if ($image) {
                    $array['logo'] = $image['filename'];
                }
            }
            News::create($array);
            View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
        }
        $vars = [
            'title' => '{{ News_Add_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Add_Block_Title }}',
                'SubmitLabel' => '{{ News_Add_Block_Submit_Title }}'
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];

        View::render($vars);
    }
    public function deleteItemAction()
    {
        extract(self::$route['vars']);
        News::remove($newsId);
        View::redirect('/news');
    }
}
