<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\GameTypes;
use app\models\Users;
use app\Repositories\PageRepository;

class GameTypesController extends Controller
{
    public function indexAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        $dashboard = '';
        if (Users::checkAccess('trusted')) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/add' title='Додати' class='fa fa-plus-square-o'></a>
                ";
            $dashboard .= '</span>';
        }
        $vars = [
            'title' => 'Games',
            'dashboard' => $dashboard,
            'games' => Locale::apply(GameTypes::names()),
            'texts' => [
                'BlockTitle' => 'Games of our club',
                'BlockSubTitle' => 'Our leisure club is going to participate in the following games',
            ],
        ];
        
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
    public function showAction()
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

        View::$route['vars']['title'] = Locale::phrase([
            'string' => 'Game «%s» - How to play?',
            'vars' => [Locale::phrase($gameNames[$game])],
        ]);

        if (Users::checkAccess('manager')) {
            View::$route['vars']['dashboard'] = [
                'id' => $page['id'],
                'slug' => $game,
            ];
        }
        
        View::$route['vars']['og'] = PageRepository::formPageOG($page);
        View::$route['vars']['page'] = $page;
        View::$route['vars']['mainClass'] = 'pages';
        
        View::$path = 'pages/show';
    
        View::render();
    }
}
