<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\GameTypes;
use app\Repositories\PageRepository;

class GameTypesController extends Controller
{
    public function indexAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['trusted', 'manager', 'admin'])) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/add' title='Додати' class='fa fa-plus-square-o'></a>
                ";
            $dashboard .= '</span>';
        }
        $vars = [
            'title' => 'Games',
            'dashboard' => $dashboard,
            'games' => Locale::apply(GameTypes::names()),
        ];
        
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
    public function gameAction()
    {
        extract(self::$route['vars']);

        $gameNames = GameTypes::names();

        if (empty($gameNames[$game]))
            View::errorCode(404, ['message' => "Game $game isn't found!"]);

        $page = PageRepository::getPage($game);

        if (empty($page)) {
            $page = PageRepository::$defaultData;
            $page['title'] = $gameNames[$game];
        }

        $vars['title'] = $gameNames[$game];

        if (!empty($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $vars['dashboard'] = (empty($page['id']) ? $game : $page['id']);
        }
        
        $vars['page'] = $page;
        $vars['mainClass'] = 'pages';
        
        View::$path = 'pages/show';

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
}
