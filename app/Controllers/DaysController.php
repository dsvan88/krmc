<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Noticer;
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
                View::message(['message' => 'Changes saved successfully!', 'url' => "/week/$weekId/day/$dayId/"]);
            }
            self::$route['action'] = 'edit';
            View::set(self::$route);
            View::$route['vars']['scripts'] = '/public/scripts/day-edit-funcs.js?v=' . $_SERVER['REQUEST_TIME'];
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
                'dayGameStart' => 'Booking time',
                'dayEvent' => 'Game’s type:',
                'ArrivePlaceHolder' => 'Arrive',
                'RemarkPlaceHolder' => 'Remark',
                'clearLabel' => 'Clear',
                'addFieldLabel' => 'Add field',
                'setDayApprovedLabel' => 'Save',
                'TimeArrivePlaceholder' => 'Arrive Time',
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
            $day['date'] = date('d.m.Y', $dayTimestamp) . ' (<strong>' . Locale::phrase(date('l', $dayTimestamp)) . '</strong>) ' . $day['time'];
        } else {
            $day['date'] = '{{ Day_Date_Not_Set }}';
        }

        $yesterday = [
            'link' => $dayId > 0 ? "/week/$weekId/day/" . ($dayId - 1).'/' : '/week/'. ($weekId-1) .'/day/6/',
            'label' => date('d.m', $dayTimestamp - TIMESTAMP_DAY),
        ];
        $tomorrow = [
            'link' => $dayId < 6 ? "/week/$weekId/day/" . ($dayId + 1).'/' : '/week/'. ($weekId+1) .'/day/0/',
            'label' => date('d.m', $dayTimestamp + TIMESTAMP_DAY),
        ];

        if (!isset($day['day_prim'])) {
            $day['day_prim'] = '';
        }

        $day['tournament'] = '';
        if (isset($day['mods']) && in_array('tournament', $day['mods'])) {
            $day['tournament'] = 'checked';
        }

        $playersCount = max(count($day['participants']), 11);

        $selfBooking = [];
        
        if (!empty($_SESSION['id'])){
            $url = self::$route['url'];
            $selfBooking = [
                'link' => "/$url/booking",
                'label' => 'Booking',
            ];
            for ($i=0; $i < $playersCount; $i++) { 
                if (empty($day['participants'][$i])) break;
                if (empty($day['participants'][$i]['id'])) continue;
                if ($day['participants'][$i]['id'] !== $_SESSION['id']) continue;
                $selfBooking = [
                    'link' => "/$url/unbooking",
                    'label' => 'Unbooking',
                ];
            }
        }

        View::$route['vars'] = array_merge(View::$route['vars'], $vars, compact('day', 'playersCount', 'gameName', 'selfBooking', 'yesterday', 'tomorrow' ));
    
        View::render();
    }
    public function selfBookingAction()
    {
        extract(self::$route['vars']);
    
        if ($bookingMode === 'booking'){
            if (Days::addParticipant($weekId, $dayId, $_SESSION['id']))
                Noticer::set('Success!');
            else 
                Noticer::set(['type'=>'error', 'message'=>'Fail!']);
        } else {
            if (Days::removeParticipant($weekId, $dayId, $_SESSION['id']))
                Noticer::set('Success!');
            else 
                Noticer::set(['type'=>'error', 'message'=>'Fail!']);
        }
        View::redirect("/week/$weekId/day/$dayId/");   
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
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
    
        View::render();
    }
}
