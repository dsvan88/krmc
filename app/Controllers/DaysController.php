<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Entities\Day;
use app\core\Locale;
use app\core\Noticer;
use app\core\Sender;
use app\core\View;
use app\Formatters\DayFormatter;
use app\Formatters\WeekFormatter;
use app\models\Days;
use app\models\GameTypes;
use app\models\Settings;
use app\models\Users;
use app\models\Weeks;
use app\Services\DayService;

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
                    $day = Day::create($dayId, $weekId);
                    Sender::message(Settings::getMainTelegramId(), DayFormatter::forMessengers($day));
                }
                return View::notice(['message' => 'Changes saved successfully!', 'location' => 'reload', 'time' => 1500]);
            }
            self::$route['action'] = 'edit';
            View::set(self::$route);
            View::$route['vars']['scripts'] = ['booking.js'];
            View::$route['vars']['styles'] = ['booking'];
        }

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
                'dayMods' => 'Peculiarities',
                'dayCosts' => 'Costs',
                'ArrivePlaceHolder' => 'Arrive',
                'RemarkPlaceHolder' => 'Remark',
                'clearLabel' => 'Clear',
                'addFieldLabel' => 'Add field',
                'setDayApprovedLabel' => 'Save',
                'TimeArrivePlaceholder' => 'Arrive Time',
            ],
        ];

        $day = Day::create($dayId, $weekId);

        $yesterday = WeekFormatter::yesterday($day);
        $tomorrow = WeekFormatter::tomorrow($day);


        $modsTexts = empty($day->mods) ?
            '' :
            '<p>' . str_replace("\n", '</p><p>', DayService::getModsTexts($day->mods)) . '</p>';

        $mods = [];
        foreach (DayService::$dayDefaultModsArray as $mod => $value)
            $mods[$mod] = in_array($mod, $day->mods) ?  'checked' : '';

        $description = DayFormatter::dayDescription($day);
        $day->participantsCount = max($day->participantsCount, 11);

        $selfBooking = [];

        if (!empty($_SESSION['id']) && !$day->isExpired()) {
            $url = self::$route['url'];
            $selfBooking = [
                'link' => "/$url/booking",
                'label' => 'Booking',
            ];
            for ($i = 0; $i < $day->participantsCount; $i++) {
                if (empty($day->participants[$i])) break;
                if (empty($day->participants[$i]['id'])) continue;
                if ($day->participants[$i]['id'] != $_SESSION['id']) continue;
                $selfBooking = [
                    'link' => "/$url/unbooking",
                    'label' => 'Unbooking',
                ];
            }
        }

        $gameTypes = GameTypes::menu();

        if (empty(View::$route['vars']['styles'])) View::$route['vars']['styles'] = ['day'];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars, compact('day', 'description', 'selfBooking', 'yesterday', 'tomorrow', 'mods', 'modsTexts', 'gameTypes'));

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

        $method = $bookingMode === 'booking' ? 'addParticipant' : 'removeParticipant';

        $day = Day::create($dayId, $weekId);

        $day->$method($_SESSION['id'])->save();

        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
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
