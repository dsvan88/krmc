<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\Locale;
use app\core\View;
use app\models\GameTypes;
use app\models\Users;
use app\Repositories\PageRepository;

class GameTypesController extends Controller
{
    public static function before(): bool
    {
        View::$route['vars']['styles'][] = 'pages';
        return true;
    }
    public function indexAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        $dashboard = '';
        if (Users::checkAccess('activist')) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/add' title='Додати' class='fa fa-plus-square-o'></a>
                ";
            $dashboard .= '</span>';
        }

        $games = GameTypes::all();

        array_walk($games, fn(&$game) => $game['data']['logo'] = empty($game['data']['logo']) ? '' : GoogleDrive::getLink($game['data']['logo']));

        $vars = [
            'title' => 'Games',
            'dashboard' => $dashboard,
            'games' => $games,
            'texts' => [
                'BlockTitle' => 'Games of our club',
                'BlockSubTitle' => 'Our leisure club is going to participate in the following games',
            ],
            'styles' => [
                'game-types'
            ]
        ];

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public function showAction()
    {
        extract(self::$route['vars']);

        $gameNames = GameTypes::names();

        if (empty($gameNames[$game]))
            return View::errorCode(404, ['message' => "Game $game isn’t found!"]);

        $page = PageRepository::getPage($game);

        $page['logoLink'] = empty($page['data']['logo']) ? '' : GoogleDrive::getLink($page['data']['logo']);

        if (empty($page)) {
            $page = PageRepository::$defaultData;
            $page['title'] = $gameNames[$game];
        }

        $vars = [
            'mainClass' => 'pages',
            'title' => Locale::phrase([
                'string' => 'Game «%s» - How to play?',
                'vars' => [Locale::phrase($gameNames[$game])],
            ]),
            'description' => $page['description'],
            'page' => $page,
            'texts' => [
                'edit' => 'Edit',
                'delete' => 'Delete',
            ],
            'og' => PageRepository::formPageOG($page),
        ];

        if (Users::checkAccess('manager')) {
            $vars['dashboard'] = [
                'id' => $page['id'],
                'slug' => $game,
            ];
        }

        View::$path = 'pages/show';
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
