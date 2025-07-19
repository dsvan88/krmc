<?php

namespace app\Controllers;

use app\core\ImageProcessing;
use app\core\Controller;
use app\core\Locale;
use app\core\Paginator;
use app\core\View;
use app\models\News;
use app\models\Settings;
use app\models\Users;
use app\Repositories\PageRepository;

class NewsController extends Controller
{
    public function indexAction()
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
        if (Users::checkAccess('manager')) {
            $setDashBoard = true;
        }

        $title = '{{ News_Show_List_Page_Title }}';
        $texts = [
            'ReadMore' => '{{ News_Block_Read_More }}'
        ];
        $paginator = Paginator::news(['page' => $page, 'count' => $newsCount]);
        View::$route['vars'] = array_merge(View::$route['vars'], compact('title', 'texts', 'newsAll', 'newsCount', 'page', 'setDashBoard', 'paginator', 'defaultImage'));
        return View::render();
    }
    public function showAction()
    {
        extract(self::$route['vars']);
        $page = News::find($newsId);
        if (!empty($page['logo']))
            $page['logo'] = ImageProcessing::inputImage(FILE_MAINGALL . 'news/' . $page['logo'], ['title' => $page['title'], 'class' => 'news__item-logo_image']);
        else
            $page['logo'] = ImageProcessing::inputImage(Settings::getImage('news_default')['value'], ['title' => Locale::phrase('{{ News_Change_Logo }}'), 'class' => 'news__item-logo_image']);

        $vars = [
            'title' => '{{ News_Show_Item_Page_Title }}',
            'newsData' => $page
        ];

        View::$route['vars']['og'] = PageRepository::formPageOG($page);
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function editAction()
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
            return View::message('Changes saved successfully!');
        }

        $newsData = News::find($newsId);
        if (!empty($newsData['logo']))
            $newsData['logo'] = ImageProcessing::inputImage(FILE_MAINGALL . 'news/' . $newsData['logo'], ['title' => $newsData['title'], 'class' => 'news__item-logo_image']);
        else
            $newsData['logo'] = ImageProcessing::inputImage(Settings::getImage('news_default')['value'], ['title' => Locale::phrase('{{ News_Change_Logo }}'), 'class' => 'news__item-logo_image']);


        $newsData['published_at'] = strtotime($newsData['published_at']);
        $newsData['published_at'] = date('Y-m-d', $newsData['published_at']) . 'T' . date('H:i', $newsData['published_at']);

        if (!empty($newsData['expired_at'])) {
            $newsData['expired_at'] = strtotime($newsData['expired_at']);
            $newsData['expired_at'] = date('Y-m-d', $newsData['expired_at']) . 'T' . date('H:i', $newsData['expired_at']);
        }

        $vars = [
            'title' => '{{ News_Edit_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Edit_Block_Title }}',
                'SubmitLabel' => 'Save'
            ],
            'newsData' => $newsData,
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
            'styles' => [
                'forms',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function editPromoAction()
    {
        if (!empty($_POST)) {
            News::edit($_POST, 'promo');
            return View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
        }
        $newsData = News::getBySlug('promo');
        $vars = [
            'title' => '{{ News_Edit_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Edit_Block_Title }}',
                'SubmitLabel' => 'Save'
            ],
            'newsData' => $newsData,
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
            'styles' => [
                'forms',
            ],
        ];

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function addAction()
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
            return View::message(['error' => 0, 'message' => 'Changes saved successfully!']);
        }
        $vars = [
            'title' => '{{ News_Add_Page_Title }}',
            'texts' => [
                'BlockTitle' => '{{ News_Add_Block_Title }}',
                'SubmitLabel' => 'Save'
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
            ],
            'styles' => [
                'forms',
            ],
        ];

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function deleteAction()
    {
        extract(self::$route['vars']);
        News::remove($newsId);
        return View::redirect('/news');
    }
}
