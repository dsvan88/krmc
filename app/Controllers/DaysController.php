<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Days;
use app\models\GameTypes;
use app\models\Settings;
use app\models\Weeks;

class DaysController extends Controller
{
    public function showAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        extract(self::$route['vars']);
        if (isset($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin', 'root'])) {
            if (!empty($_POST)) {
                $weekId = Days::edit($weekId, $dayId, $_POST);
                if (!empty($_POST['send'])){
                    $weekData = Weeks::weekDataById($weekId);
                    $result = TelegramBotController::send(Settings::getMainTelegramId(), Days::getFullDescription($weekData,$dayId));
                }
                View::message(['message' => '{{ Day_Set_Success }}', 'url' => "/week/$weekId/day/$dayId/"]);
            }
            self::$route['action'] = 'edit';
            View::set(self::$route);
        }
        $gameTypes = GameTypes::menu();

        $vars = [
            'title' => '{{ Day_Set_Page_Title }}',
            'texts' => [
                'daysBlockTitle' => '{{ Day_Block_Title }}',
                'dayStartTime' => '{{ Day_Block_Start_Time }}',
                'daysBlockParticipantsTitle' => '{{ Day_Block_Participants_Title }}',
                'dayTournamentCheckboxLabel' => 'Tournament',
                'daySendCheckboxLabel' => 'Send to chat',
                'dayGameStart' => '{{ Day_Block_Games_Start }}',
                'dayEvent' => '{{ Day_Block_Game_Name }}',
                'dayRemarkPlaceHolder' => '{{ Day_Block_Prim_PLaceholder }}',
                'clearLabel' => 'Clear',
                'addFieldLabel' => '{{ Add_Field_Label }}',
                'setDayApprovedLabel' => 'Save',
            ],
            'gameTypes' => $gameTypes,
        ];

        $day = Days::weekDayData($weekId, $dayId);

        $day['weekId'] = $weekId;
        $day['dayId'] = $dayId;

        $dayDefaultData = Days::$dayDataDefault;

        if (!isset($day['time'])) {
            $day['time'] = $dayDefaultData['time'];
        }

        $gamesCount = count($gameTypes);
        for ($i = 0; $i < $gamesCount; $i++) {
            if ($gameTypes[$i]['slug'] !== $day['game']) continue;
            $gameName = Locale::phrase($gameTypes[$i]['name']);
            break;
        }

        if (isset($day['weekStart'])) {
            $dayTimestamp = $day['weekStart'] + TIMESTAMP_DAY * $dayId;
            $day['date'] = date('d.m.Y', $dayTimestamp) . ' (<strong>' . Locale::phrase('{{ ' . date('l', $dayTimestamp) . ' }}') . '</strong>) ' . $day['time'];
        } else {
            $day['date'] = '{{ Day_Date_Not_Set }}';
        }

        if (!isset($day['day_prim'])) {
            $day['day_prim'] = '';
        }

        $day['tournament'] = '';
        if (isset($day['mods']) && in_array('tournament', $day['mods'])) {
            $day['tournament'] = 'checked';
        }

        $playersCount = max(count($day['participants']), 11);
        $scripts = '/public/scripts/day-edit-funcs.js?v=' . $_SERVER['REQUEST_TIME'];
        $vars = array_merge($vars, compact('day', 'playersCount', 'scripts', 'gameName'));
        View::render($vars);
    }
    public function addAction()
    {
        $vars = [
            'title' => '{{ Day_Block_Title }}',
            'texts' => [
                'dayStartTime' => '{{ Day_Block_Start_Time }}',
                'daysBlockParticipantsTitle' => '{{ Day_Block_Participants_Title }}',
            ]
        ];
        View::render($vars);
    }
}
