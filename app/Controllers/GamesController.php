<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Days;
use app\models\Games;
use app\models\Users;
use app\models\Weeks;


class GamesController extends Controller
{
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
            'managerPlaceholder' => 'Manager',
            'playerPlaceholder' => 'Nickname',
            'Start' => 'Start',
            'addPlayer' => 'Add Player',
        ];

        $day = Days::weekDayData($weekId, $dayId);

        $needed = 16;
        $count  = count($day['participants']);
        $manager = '';
        if ( $count < $needed){
            $participants = [];
            $_participants = array_merge($day['participants'], Users::random($needed - $count));
            $day['participants'] = [];
            foreach($_participants as $participant){
                if (empty($manager) && $participant['id'] === $_SESSION['id']){
                    $manager = $participant['name'];
                }
                if (in_array($participant['name'], $participants)) continue;
                $participants[] = $participant['name'];
                array_push($day['participants'], $participant);
            }
        }
        
        $shuffled = array_map(fn($value): string => $value['name'], $day['participants']);
        shuffle($shuffled);

        $vars = [
            'title' => 'Prepeare a game',
            'texts' => $texts,
            'day' => $day,
            'manager' => $manager,
            'shuffled' => $shuffled,
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
            'managerPlaceholder' => 'Manager',
            'playerPlaceholder' => 'Nickname',
            'Start' => 'Start',
        ];

        $vars = [
            'title' => 'Play a game',
            'texts' => $texts,
            'game' => $game,
            'scripts' => [
                '/public/scripts/prompt.js?v=' . $_SERVER['REQUEST_TIME'],
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