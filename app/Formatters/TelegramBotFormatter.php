<?php

namespace app\Formatters;

use app\core\Entities\Day;
use app\core\Entities\Week;
use app\core\Locale;
use app\mappers\Days;
use app\mappers\Weeks;
use app\Services\TelegramBotService;

class TelegramBotFormatter
{
    public static function getForwardDaysListMarkup(string $callback = 'unreg', bool $all = false): array
    {
        $day = $curr = Day::current();
        $days = [];
        for ($x = 0; $x < 7; $x++) {
            $day = $day++;
            if ($day === 7) $day = 0;
            $days[] = $day++;
        }
        $dayNames = Locale::apply(Days::$days);
        $weekId = Weeks::currentId();
        $week = Week::create($weekId);

        $inline_keyboard = [];
        foreach ($days as $dayNum) {
            if ($dayNum < $curr) {
                $weekId++;
                $week = Weeks::create($weekId);
                if (empty($week)) break;
            }
            if (!$all && $week->days[$dayNum]->status !== 'set') continue;
            $inline_keyboard[] = [['text' => $week->days[$dayNum]->dayName . ' - ' . $week->days[$dayNum]->gameName, 'callback_data' => ['c' => $callback, 'w' => $weekId, 'd' => $dayNum]]];
        }
        return compact('inline_keyboard');
    }
    public static function getPaticipantsListMarkup(string $callback = 'unreg', int $weekId = 0, int $dayId = 0): array
    {
        $day = Day::create($dayId, $weekId);
        if (empty($day->participants)) {
            return [];
        }

        $inline_keyboard = [];
        foreach ($day->participants as $participant) {
            if (empty($participant['name'])) continue;
            $inline_keyboard[] = [['text' => $participant['name'], 'callback_data' => ['c' => $callback, 'w' => $weekId, 'd' => $dayId, 'u' => $participant['id']]]];
        }
        return compact('inline_keyboard');
    }
    public static function getBookingMarkup(int $weekId, int $dayNum, bool $booked = false, bool $full = false): array
    {
        if (TelegramBotService::isDirect() && $booked) {
            return [
                'inline_keyboard' => [
                    [
                        ['text' => '❌' . Locale::phrase('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]],
                        ['text' => '♻️', 'callback_data' => ['c' => 'refresh', 'w' => $weekId, 'd' => $dayNum]]
                    ]
                ]
            ];
        }

        $result = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . Locale::phrase('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                    ['text' => Locale::phrase('I want!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ['text' => '♻️', 'callback_data' => ['c' => 'refresh', 'w' => $weekId, 'd' => $dayNum]],
                ],
            ],
        ];

        if ($full || !TelegramBotService::isDirect()) {
            $result['inline_keyboard'][0][] = ['text' => '❌' . Locale::phrase('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]];
        }

        return $result;
    }
}