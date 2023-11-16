<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Paginator;
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

        usort($day['participants'], function ($participantA, $participantB){
            return $participantA['name'] > $participantB['name'] ? 1 : -1;
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
            "gamePass" => '1234',
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
            $settings['mafia-config']['options']['points'] = array_merge($config['points'], $settings['mafia-config']['options']['points']);
            $config = array_merge($config, $settings['mafia-config']['options']);
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
                'mafia/mafia-roles-pad.js',
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
    public function historyAction(){

        extract(self::$route['vars']);

        $weekCurrentId = Weeks::currentId();

        if (empty($weekId)){
            $weekId = $weekCurrentId;
        }

        $week = Weeks::weekDataById($weekId);
        $weeksIds = Weeks::getIds();
        $weeksCount = count($weeksIds);
        $weekCurrentIndexInList = array_search($weekCurrentId, $weeksIds);
        $selectedWeekIndex = array_search($weekId, $weeksIds);
        $paginator = Paginator::games(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $games = Games::getAll(['week_id' => $weekId]);

        usort($games, function ($gameA, $gameB){
            return $gameA['id'] > $gameB['id'] ? -1 : 1;
        });

        $countGames = count($games);
        for ($x=0; $x < $countGames; $x++) { 
            $games[$x] = Games::decodeJson($games[$x]);
            $games[$x]['class'] = '';
            if ($games[$x]['win'] === '1'){
                $games[$x]['class'] = 'peace';
            }
            else if ($games[$x]['win'] === '2'){
                $games[$x]['class'] = 'mafia';
            }
            elseif ($games[$x]['win'] === '3'){
                $games[$x]['class'] = 'even';
            }
        }

        $vars = [
            'title' => 'Games history',
            'week' => $week,
            'weeksCount' => $weeksCount,
            'games' => $games,
            'paginator' => $paginator,
            'texts' => [
                'BlockTitle' => 'Games history',
            ],
            'scripts' => 'games-history.js',
            'teams' => ['In progress', 'Peace', 'Mafia', 'Even'],
        ];
        
        View::$route['vars']['prevWeek'] = false;
        View::$route['vars']['nextWeek'] = false;

        if (isset($weeksIds[$selectedWeekIndex - 1]))
            View::$route['vars']['prevWeek'] = Weeks::weekDataById($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            View::$route['vars']['nextWeek'] = Weeks::weekDataById($weeksIds[$selectedWeekIndex + 1]);

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::render();
    }
    public function historyItemAction(){
        extract(self::$route['vars']);
        $game = Games::find($gameId);
        if (!$game) {
            View::errorCode(404, ['message' => "Game with id: $gameId is not found"]);
        }

        View::$route['vars']['game'] = json_decode($game['state'], true);
        View::$route['vars']['path'] = 'components/game-card';
        View::html();
    }
    public function showAction(){
        extract(self::$route['vars']);
        $game = Games::find($gameId);
        if (!$game) {
            View::errorCode(404, ['message' => "Game with id: $gameId is not found"]);
        }
        View::$route['vars']['title'] = 'Гра';
        View::$route['vars']['state'] = json_decode($game['state'], true);
        View::$route['vars']['players'] = json_decode($game['players'], true);
        View::render();
    }
    public function peekAction(){
        $game = Games::last();
        View::redirect('/game/show/mafia/'.$game['id']);
    }
    public function ratingAction(){
        extract(self::$route['vars']);

        $weekCurrentId = Weeks::currentId();

        if (empty($weekId)){
            $weekId = $weekCurrentId;
        }

        $week = Weeks::weekDataById($weekId);
        $weeksIds = Weeks::getIds();
        $weeksCount = count($weeksIds);
        $weekCurrentIndexInList = array_search($weekCurrentId, $weeksIds);
        $selectedWeekIndex = array_search($weekId, $weeksIds);
        $paginator = Paginator::games(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $games = Games::getAll(['week_id' => $weekId]);

        $rating = [];
        $ratingDefault = [
            'points' => 0,
            'pointsLog' => [],
            'win' => [
                'don' => 0,
                'sherif' => 0,
                'mafia' => 0,
                'peace' => 0,
                'all' => 0,
            ],
            'pointTypes' => [
                'Winners' => 0,
                'BestMove' => 0,
                'positive' => 0,
                'negative' => 0,
            ],
            'games' => 0,
        ];
        $countGames = count($games);
        for ($x=0; $x < $countGames; $x++) { 
            $games[$x] = Games::decodeJson($games[$x]);
            foreach($games[$x]['players'] as $player){
                if (empty($games[$x]['win'])) continue;
                if (empty($rating[$player['id']]))
                    $rating[$player['id']] = $ratingDefault;

                $rating[$player['id']]['name'] = $player['name'];
                $rating[$player['id']]['games']++;
                $points = $player['points'] + $player['adds'];
                $rating[$player['id']]['points'] += $points;
                $rating[$player['id']]['pointsLog'][] = $player['pointsLog'];
                if ($games[$x]['win'] === '1' && in_array($player['role'], ['peace', 'sherif'], true) || $games[$x]['win'] === '2' && in_array($player['role'], ['mafia', 'don'], true)){
                    $rating[$player['id']]['win'][$player['role']]++;
                    $rating[$player['id']]['win']['all']++;
                }
                $countLogs = count($player['pointsLog']);
                for ($y=0; $y < $countLogs; $y++) { 
                    foreach($player['pointsLog'][$y] as $index=>$value){
                        if (in_array($index, ['Winners', 'BestMove'])){
                            $rating[$player['id']]['pointTypes'][$index] += $value;
                            continue;
                        }
                        if (in_array($index, ['AliveMafia', 'AliveRed', 'FirstKillSherifDynamic', 'FirstKillSherifStatic'], true)){
                            $rating[$player['id']]['pointTypes']['positive'] += $value;
                            continue;
                        }
                        if (in_array($index, ['Disqualification', 'FourFouls', 'VotedInSherif'])){
                            $rating[$player['id']]['pointTypes']['negative'] += $value;
                            continue;
                        }
                    }
                }
                
            }
        }

        usort($rating, function ($playerA, $playerB){
            return $playerA['points'] > $playerB['points'] ? -1 : 1;
        });

        $vars = [
            'title' => 'Games rating',
            'week' => $week,
            'weeksCount' => $weeksCount,
            'rating' => $rating,
            'paginator' => $paginator,
            'texts' => [
                'BlockTitle' => 'Games rating',
            ],
        ];
        
        View::$route['vars']['prevWeek'] = false;
        View::$route['vars']['nextWeek'] = false;

        if (isset($weeksIds[$selectedWeekIndex - 1]))
            View::$route['vars']['prevWeek'] = Weeks::weekDataById($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            View::$route['vars']['nextWeek'] = Weeks::weekDataById($weeksIds[$selectedWeekIndex + 1]);

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::render();
    }
    public function lastAction(){
        $game = Games::last();
        
        if (empty($game)) View::redirect();

        if ($game['win'] < 1 && Users::checkAccess('trusted'))
            View::redirect('/game/mafia/'.$game['id']);

        View::redirect('/game/show/mafia/'.$game['id']);
    }
}
