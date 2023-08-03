<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\GameTypes;

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
        View::render($vars);
    }
    public function gameAction()
    {
        extract(self::$route['vars']);
        $gameName = GameTypes::names()[$game];
        $texts = [
            'title' => 'No data',
            'subtitle' => 'No data',
            'content' => 'No data',
        ];

        $gameData = GameTypes::findBy('slug', $game);
        if ($gameData){
            $gameData = $gameData[0];
            $texts = [
                'title' => $gameData['title'],
                'subtitle' => $gameData['subtitle'],
                'content' => $gameData['html'],
            ];
        }

        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['trusted', 'manager', 'admin'])) {
            $id = isset($gameData['id']) ? $gameData['id'] : $game;
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/edit/{$id}' title='Редагувати'><i class='fa fa-pencil-square-o'></i></a>
                <a href='/page/delete/{$id}' onclick='return confirm(\"Are you sure?\")' title='Видалити'><i class='fa fa-trash-o'></i></a>";
            $dashboard .= '</span>';
        }

        $texts['title'] .= $dashboard;

        $vars = [
            'title' => $gameName,
            'texts' => $texts,
        ];
        View::render($vars);
    }
}