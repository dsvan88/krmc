<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Noticer;
use app\core\Sender;
use app\core\View;
use app\models\Days;
use app\models\GameTypes;
use app\models\Settings;
use app\models\Users;
use app\models\Weeks;
use app\Repositories\DayRepository;

class DaysController extends Controller
{
    public function showAction()
    {
        // Extract $weekId & $dayId from array self::$route['vars']
        extract(self::$route['vars']);
        if (Users::checkAccess('manager')) {
            if (!empty($_POST)) {
                $weekId = Days::edit($weekId, $dayId, $_POST);
                if (!empty($_POST['send'])) {
                    $weekData = Weeks::weekDataById($weekId);
                    $result = Sender::message(Settings::getMainTelegramId(), Days::getFullDescription($weekData, $dayId));
                }
                return View::message(['message' => 'Changes saved successfully!', 'url' => "/week/$weekId/day/$dayId/"]);
            }
            self::$route['action'] = 'edit';
            View::set(self::$route);
            View::$route['vars']['scripts'] = ['day-edit-funcs.js'];
        }
        $gameTypes = GameTypes::menu();

        $vars = [
            'title' => '{{ Day_Set_Page_Title }}',
            'texts' => [
                'daysBlockTitle' => 'Event',
                'dayStartTime' => 'Start',
                'daySettingsLegend' => 'Settings',
                'daysBlockParticipantsTitle' => 'Participants',
                'dayTournamentCheckboxLabel' => 'Tournament',
                'daySendCheckboxLabel' => 'Send to chat',
                'dayGameStart' => 'Booking time',
                'dayEvent' => 'Game’s type',
                'dayMods' => 'Game’s mods',
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
            $day['gameName'] = Locale::phrase($gameTypes[$i]['name']);
            break;
        }

        $dayTimestamp = $day['weekStart'] + TIMESTAMP_DAY * $dayId;
        $day['date'] = empty($day['weekStart']) ? '{{ Day_Date_Not_Set }}' : date('d.m.Y', $dayTimestamp) . ' (<strong>' . Locale::phrase(date('l', $dayTimestamp)) . '</strong>) ';
        $day['dateTime'] = empty($day['weekStart']) ? '{{ Day_Date_Not_Set }}' : $day['date'] . $day['time'];

        if ($dayId == 0 && !Weeks::checkPrevWeek($weekId)) {
            $yesterday = [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];
        } else {
            $yesterday = [
                'link' => $dayId > 0 ? "/week/$weekId/day/" . ($dayId - 1) . '/' : '/week/' . ($weekId - 1) . '/day/6/',
                'label' => date('d.m', $dayTimestamp - TIMESTAMP_DAY),
            ];
        }

        if ($dayId == 6 && !Weeks::checkNextWeek($weekId)) {
            $tomorrow = [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];
        } else {
            $tomorrow = [
                'link' => $dayId < 6 ? "/week/$weekId/day/" . ($dayId + 1) . '/' : '/week/' . ($weekId + 1) . '/day/0/',
                'label' => date('d.m', $dayTimestamp + TIMESTAMP_DAY),
            ];
        }

        $day['day_prim'] = empty($day['day_prim']) ? '' : str_replace("\n", '  ', $day['day_prim']);

        $day['tournament'] = '';
        if (isset($day['mods']) && in_array('tournament', $day['mods'])) {
            $day['tournament'] = 'checked';
        }

        $description = DayRepository::dayDescription($day);
        $playersCount = max(count($day['participants']), 11);

        $selfBooking = [];

        if (!empty($_SESSION['id']) && !Days::isExpired($dayTimestamp)) {
            $url = self::$route['url'];
            $selfBooking = [
                'link' => "/$url/booking",
                'label' => 'Booking',
            ];
            for ($i = 0; $i < $playersCount; $i++) {
                if (empty($day['participants'][$i])) break;
                if (empty($day['participants'][$i]['id'])) continue;
                if ($day['participants'][$i]['id'] != $_SESSION['id']) continue;
                $selfBooking = [
                    'link' => "/$url/unbooking",
                    'label' => 'Unbooking',
                ];
            }
        }

        View::$route['vars'] = array_merge(View::$route['vars'], $vars, compact('day', 'playersCount', 'description', 'selfBooking', 'yesterday', 'tomorrow'));

        return View::render();
    }
    public function nearAction()
    {
        [$weekId, $dayId] = Days::near();
        return View::redirect(empty($dayId) ? "/weeks/$weekId/" : "/week/$weekId/day/$dayId/");
    }
    public function selfBookingAction()
    {
        extract(self::$route['vars']);

        if ($bookingMode === 'booking') {
            if (Days::addParticipant($weekId, $dayId, $_SESSION['id']))
                Noticer::set('Success!');
            else
                Noticer::set(['type' => 'error', 'message' => 'Fail!']);
        } else {
            if (Days::removeParticipant($weekId, $dayId, $_SESSION['id']))
                Noticer::set('Success!');
            else
                Noticer::set(['type' => 'error', 'message' => 'Fail!']);
        }
        return View::redirect("/week/$weekId/day/$dayId/");
    }
    public function addAction()
    {
        $vars = [
            'title' => 'Event',
            'texts' => [
                'dayStartTime' => 'Start',
                'daysBlockParticipantsTitle' => 'Participants',
            ]
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
}
