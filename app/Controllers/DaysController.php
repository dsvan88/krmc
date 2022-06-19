<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Days;
use app\models\Users;
use app\models\Weeks;

class DaysController extends Controller
{
    public function showAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        extract(self::$route['vars']);
        if (isset($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) {
            if (!empty($_POST)) {
                $weekId = self::edit($_POST);
                View::message(['error' => 0, 'message' => '{{ Day_Set_Success }}', 'url' => "/days$dayId/w$weekId"]);
            }
            self::$route['action'] = 'edit';
            View::set(self::$route);
        }
        $vars = [
            'daysBlockContent' => '',
            'texts' => [
                'daysBlockTitle' => '{{ Day_Block_Title }}',
                'dayStartTime' => '{{ Day_Block_Start_Time }}',
                'daysBlockParticipantsTitle' => '{{ Day_Block_Participants_Title }}',
                'dayTournamentCheckboxLabel' => '{{ Day_Block_Tournament_Checkbox_Label }}',
                'dayGameStart' => '{{ Day_Block_Games_Start }}',
                'dayGameMafia' => '{{ Mafia }}',
                'dayGamePoker' => '{{ Poker }}',
                'dayGameBoard' => '{{ Board }}',
                'dayGameCash' => '{{ Cash }}',
                'dayGameEtc' => '{{ Etc }}',
                'dayEvent' => '{{ Day_Block_Game_Name }}',
                'dayRemarkPlaceHolder' => '{{ Day_Block_Prim_PLaceholder }}',
                'clearLabel' => '{{ Clear_Label }}',
                'addFieldLabel' => '{{ Add_Field_Label }}',
                'setDayApprovedLabel' => '{{ Save_Label }}',
            ]
        ];

        $day = Days::weekDayData($weekId, $dayId);

        $day['weekId'] = $weekId;
        $day['dayId'] = $dayId;

        $dayDefaultData = Days::$dayDataDefault;

        if (!isset($day['time'])) {
            $day['time'] = $dayDefaultData['time'];
        }

        if (isset($day['weekStart'])) {
            $dayTimestamp = $day['weekStart'] + TIMESTAMP_DAY * $dayId;
            $day['date'] = date('d.m.Y', $dayTimestamp) . ' (<strong>' . Locale::applySingle('{{ ' . date('l', $dayTimestamp) . ' }}') . '</strong>) ' . $day['time'];
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
        $vars = array_merge($vars, compact('day', 'playersCount', 'scripts'));
        View::render('{{ Day_Set_Page_Title }}', $vars);
    }
    public function addAction()
    {
        $vars = [
            'daysBlockContent' => print_r(Days::$dayDataDefault, true),
            'texts' => [
                'daysBlockTitle' => '{{ Day_Block_Title }}',
                'dayStartTime' => '{{ Day_Block_Start_Time }}',
                'daysBlockParticipantsTitle' => '{{ Day_Block_Participants_Title }}',
            ]
        ];
        View::render('{{ Week_Set_Page_Title }}', $vars);
    }
    public function edit($data)
    {
        try {
            extract(self::$route['vars']);
            $newData = [
                'time' => trim($data['day_time']),
                'game' => trim($data['game']),
                'day_prim' => trim($data['day_prim']),
                'status' => 'set'
            ];
            if (isset($data['mods'])) {
                $newData['mods'] = $data['mods'];
            }
            $newData['participants'] = [];
            for ($i = 0; $i < count($data['participant']); $i++) {
                $name = trim($data['participant'][$i]);
                if ($name === '') continue;

                if ($name !== '+1') {
                    $id = Users::getId(trim($data['participant'][$i]));
                    if ($id < 2) {
                        $id = Users::add($name);
                    }
                } else {
                    $name = 'tmp_user_' . $i;
                    $id = -1;
                }
                $newData['participants'][] = [
                    'id' => $id,
                    'name' => $name,
                    'arrive' => trim($data['arrive'][$i]),
                    'prim' => trim($data['prim'][$i]),
                ];
            }
            return Days::setDayData($weekId, $dayId, $newData);
        } catch (\Throwable $th) {
            error_log(__METHOD__ . $th->__toString());
            return false;
        }
    }
}
