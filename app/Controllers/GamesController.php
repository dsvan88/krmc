<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Days;
use app\models\Games;
use app\models\Settings;
use app\models\Users;
use app\models\Weeks;
use Throwable;

class GamesController extends Controller
{
    public function prepeareAction()
    {
        extract(self::$route['vars']);
        if (!empty($_POST)) {
            try {
                $gameId = Games::create($_POST);
            }
            catch(Throwable $th){
                View::message('Fail!');
            }
            View::location('/game/mafia/' . $gameId);
        }

        $weekId = Weeks::currentId();
        $dayId = Days::current();

        $texts = [
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'gameSettingsTitle' => 'Game Settings',
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

        if ($count < $needed) {
            $participants = [];
            $_participants = array_merge($day['participants'], Users::random($needed - $count));
            $day['participants'] = [];
            foreach ($_participants as $participant) {
                if (empty($manager) && $participant['id'] === $_SESSION['id']) {
                    $manager = $participant['name'];
                }
                if (!empty($participant['id']) && in_array($participant['name'], $participants)) continue;
                $participants[] = $participant['name'];
                array_push($day['participants'], $participant);
            }
        }

        $shuffled = [];
        array_walk($day['participants'], function ($element) use (&$shuffled, $manager): string {
            if (in_array($element['name'], [$manager, '+1', '&lt; Deleted &gt;'])) return false;
            $shuffled[] = $element['name'];
            return true;
        });
        shuffle($shuffled);

        $config = [
            "voteType" => "enum",
            "courtAfterFouls" => false,
            "getOutHalfPlayersMin" => 4,
            "mutedSpeakMaxCount" => 5,
            "bestMovePlayersMin" => 9,
            "timerMax" => 6000,
            "lastWillTime" => 6000,
            "debateTime" => 3000,
            "mutedSpeakTime" => 3000,
            "wakeUpRoles" => 2000,
            "points" => [
                "winner" => 1,
                "sherifFirstStaticKill" => 0.1,
                "sherifFirstDynamicKill" => 0.3,
                "bestMove" => [0, 0, 0.25, 0.4],
                "aliveMafs" => [0, 0, 0.25, 0.4],
                "aliveReds" => [0, 0, 0.15, 0.1],
                "fourFouls" => -0.1,
                "disqualified" => -0.3,
                "voteInSherif" => -0.1,
            ],
        ];
        
        $settings = Settings::getGroup('mafia_config');
        if (!empty($settings)){
            $config = $settings['mafia-config']['options'];
        }

        $vars = [
            'title' => 'Prepeare a game',
            'texts' => $texts,
            'day' => $day,
            'manager' => $manager,
            'shuffled' => $shuffled,
            'maxPlayers' => 10,
            'playersCount' => count($day['participants']),
            'config' => $config,
            'scripts' => [
                'manager-game-funcs.js',
            ],
        ];

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
    public function playAction()
    {
        extract(self::$route['vars']);

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
            'scripts' => [
                'manager-game-funcs.js',
                'mafia/player.class.js',
                'mafia/game-engine.class.js',
                'mafia/mafia-engine.class.js',
                'mafia/timer.class.js',
                'mafia/game.js',
                'mafia/mafia-vote-numpad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
    public function saveAction()
    {
        extract(self::$route['vars']);
        $game = Games::find($gameId);
        if (!$game) {
            View::message("Game with id: $gameId is not found");
        }
        Games::save($_POST, $gameId);
        View::response(json_encode($game, JSON_UNESCAPED_UNICODE));
    }
    public function loadAction()
    {
        extract(self::$route['vars']);
        $game = Games::load($gameId);
        View::response(json_encode($game, JSON_UNESCAPED_UNICODE));
    }
}
