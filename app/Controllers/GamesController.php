<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Days;
use app\models\Games;
use app\models\Pages;
use app\models\Users;
use app\models\Weeks;

class GamesController extends Controller
{
    public static $mainTable = 'games';

    public function indexAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            $dashboard = "<span class='page__dashboard' style='float:right'>
                <a href='/page/add' title='Додати' class='fa fa-plus-square-o'></a>
                ";
            $dashboard .= '</span>';
        }
        $vars = [
            'title' => 'Games',
            'dashboard' => $dashboard,
            'games' => Locale::apply(Games::names()),
        ];
        View::render($vars);
    }
    public function gameAction()
    {
        extract(self::$route['vars']);
        $gameName = Games::names()[$game];
        $texts = [
            'title' => 'No data',
            'subtitle' => 'No data',
            'content' => 'No data',
        ];

        $gameData = Pages::findBy('slug', $game);
        if ($gameData){
            $gameData = $gameData[0];
            $texts = [
                'title' => $gameData['title'],
                'subtitle' => $gameData['subtitle'],
                'content' => $gameData['html'],
            ];
        }

        $dashboard = '';
        if (isset($_SESSION['privilege']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
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
    public function prepeareAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)){
            $gameId = Games::create($_POST);
            View::location('/game/mafia/'.$gameId);
        }

        $weekId = Weeks::currentId();
        $dayId = Days::current();

        $texts = [
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'content' => 'Content',
            'managerPlaceholder' => 'Managing',
            'playerPlaceholder' => 'Nickname',
            'Start' => 'Start',
        ];


        // $day = Days::weekDayData($weekId, $dayId);
        $day['participants'] = Users::random(15);

        $vars = [
            'title' => 'Prepeare a game',
            'texts' => $texts,
            'day' => $day,
            'maxPlayers' => 10,
            'playersCount' => count($day['participants']),
            'scripts' => [
                '/public/scripts/manager-game-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        
        View::render($vars);
    }
    public function playAction()
    {
        extract(self::$route['vars']);

        $game = Games::find($gameId);

        $texts = [
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'content' => 'Content',
            'managerPlaceholder' => 'Managing',
            'playerPlaceholder' => 'Nickname',
            'Start' => 'Start',
        ];


        // $day = Days::weekDayData($weekId, $dayId);
        $day['participants'] = Users::random(15);

        $vars = [
            'title' => 'Prepeare a game',
            'texts' => $texts,
            'game' => $game,
            'scripts' => [
                '/public/scripts/manager-game-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/mafia/player.class.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/mafia/game-engine.class.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/mafia/mafia-engine.class.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/mafia/timer.class.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/mafia/game.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public function saveAction(){
        extract(self::$route['vars']);
        $game = Games::find($gameId);
        if (!$game){
            View::message("Game with id: $gameId is not found");
        }
        Games::save($_POST, $gameId);
        View::response(json_encode($game, JSON_UNESCAPED_UNICODE));
    }
    public function loadAction(){
        extract(self::$route['vars']);
        $game = Games::find($gameId);
        View::response(json_encode($game, JSON_UNESCAPED_UNICODE));
    }
}