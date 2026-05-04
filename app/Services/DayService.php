<?php

namespace app\Services;

use app\core\Entities\Day;
use app\core\Entities\Week;
use app\core\Locale;
use app\core\Mailer;
use app\core\Tech;
use app\core\Validator;
use app\mappers\Settings;
use app\mappers\TelegramChats;
use app\mappers\Users;
use app\mappers\Weeks;

class DayService
{
    public static $daysArray = [
        ['пн', 'пон', 'mon'],
        ['вт', 'вто', 'вів', 'tue'],
        ['ср', 'сре', 'сер', 'wed'],
        ['чт', 'чтв', 'чет', 'thu'],
        ['пт', 'пят', 'п’ят', 'fri'],
        ['сб', 'суб', 'sat'],
        ['вс', 'вос', 'нед', 'нд', 'sun']
    ];

    public static $dayDefaultModsArray = [
        'beginners' => '',
        'tournament' => '',
        'night' => '',
        'close' => '',
        'theme' => '',
        'funs' => '',
        'sales' => '',
    ];
    public static $techDaysArray = [
        'today' => ['tod', 'td', 'сг', 'сег', 'сьо'],
        'tomorrow' => ['tom', 'tm', 'зав'],
    ];

    public static function edit(int $weekId, int $dayId, array $data)
    {
        $day = Day::create($dayId, $weekId);
        $day->game = trim($data['game']);
        $day->mods = $data['mods'] ?? [];
        $day->time = trim($data['day_time']);
        $day->status = 'set';
        $day->participants = [];
        $day->day_prim = str_replace('  ', "\n", trim($data['day_prim']));
        $day->cost = [
            'amount' => trim($data['cost_amount']),
            'currency' => trim($data['cost_currency']),
            'type' => trim($data['cost_type']),
        ];
        $_type = $day->cost['type'] === 'day' ? Locale::phrase('evening') : Locale::phrase('game');
        $day->costText = "{$day->cost['amount']} {$day->cost['currency']} for $_type";

        foreach ($data['participant'] as $i => $participant) {
            if (empty($participant)) continue;
            if ($participant === '+1') {
                $id = null;
            } elseif ($participant[0] === '@') {
                $tgName = substr($participant, 1);
                $chatData = TelegramChats::findByUserName($tgName);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['id'];
            } elseif ($participant[0] === '_') {
                $tgChatId = substr($participant, 1);
                $chatData = TelegramChats::find($tgChatId);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['id'];
            } else {
                $id = Users::getId($participant);
                if ($id < 2) {
                    $name = Validator::validate('name', $participant);

                    if (empty($name)) continue;

                    $id = Users::getId($name);
                    if ($id < 2) {
                        $id = Users::add($name);
                    }
                }
            }

            $participant = [
                'userId' => $id,
                'arrive' => trim($data['arrive'][$i]),
                'prim' => trim($data['prim'][$i]),
            ];
            $day->addParticipant($participant);
        }
        $day->save();

        return $day;
    }
    public static function renamePlayer(int $userId, string $name): void
    {
        $weeks = Weeks::getAll();
        foreach ($weeks as $week) {
            foreach ($week['data'] as $dayNum => $day) {
                foreach ($day['participants'] as $participantNum => $participant) {
                    if ($participant['id'] !== $userId) continue;
                    $week['data'][$dayNum]['participants'][$participantNum]['name'] = $name;
                }
            }
            Weeks::setWeekData($week['id'], ['data' => $week['data']]);
        }
    }
    public static function dayDescription(array $day): string
    {
        if (empty($day)) return false;
        $result = $day['date'] . ' - ' .  $day['gameName'] . "\n" . Locale::phrase('Already registered players') . ': ' . count($day['participants']) . PHP_EOL;
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function findNearSetDay(int $weekId, int $dayId): ?Day
    {
        Day::$all = true;
        do {
            ++$dayId;
            if ($dayId > 6) {
                if (!Weeks::checkNextWeek($weekId, true)) return null;
                $dayId = 0;
                ++$weekId;
            }
            $day = Day::create($dayId, $weekId);
        } while ($day->status !== 'set');

        return $day;
    }
    public static function getDayNamesForCommand(): string
    {
        $days = [];
        foreach (static::$daysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        foreach (static::$techDaysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        return implode('|', $days);
    }
    public static function findLastGameOfPlayer($userId = 0)
    {
        if (empty($userId)) return 0;

        $weeks = Weeks::getAll();
        $weeks = array_reverse($weeks);
        $statuses = ['set', 'finished'];
        foreach ($weeks as $week) {
            foreach ($week['data'] as $num => $day) {
                if (!in_array($day['status'],  $statuses, true)) continue;
                foreach ($day['participants'] as $player) {
                    if ($player['id'] == $userId)
                        return $week['start'] + TIMESTAMP_DAY * $num;
                }
            }
        }

        return 0;
    }
    public static function findBookedDays($userId = 0, int $limitWeeks = 0): array
    {
        if (empty($userId)) return [];

        $currentWeekId = Weeks::currentId();

        $weeks = Weeks::getAll();
        $weeks = array_reverse($weeks);
        $statuses = ['set', 'finished'];
        $result = [];
        foreach ($weeks as $week) {
            if (!empty($limitWeeks) && $week['id'] < $currentWeekId - $limitWeeks) break;
            foreach ($week['data'] as $num => $day) {
                if (!in_array($day['status'],  $statuses, true)) continue;
                foreach ($day['participants'] as $index => $player) {
                    if ($player['id'] == $userId) {
                        $result[] = [
                            'week' => $week['id'],
                            'day' => $num,
                            'index' => $index,
                        ];
                        break;
                    }
                }
            }
        }

        return $result;
    }
    public static function changeParticipantId(array $data = [], int $userId = 0): void
    {
        if (empty($data) || empty($userId)) return;

        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $week = Weeks::find($data[$i]['week']);
            $week['data'][$data[$i]['day']]['participants'][$data[$i]['index']]['id'] = $userId;
            Weeks::update(['data' => json_encode($week['data'], JSON_UNESCAPED_UNICODE)], ['id' => $week['id']]);
        }
    }
    public static function finishExpiredDays(): void
    {
        $today = Day::current();
        $weekId = Weeks::currentId();
        if ($today < 2) {
            --$weekId;
        }
        $week = Week::create($weekId);
        foreach ($week->days as $i => $day) {
            if ($weekId === Weeks::currentId() && $i >= $today) break;
            SocialPointsService::applyOnDay($day);
            CouponService::burn($day);
        }
        $week->save();

        if ($_SESSION['report']) {
            $settings = Settings::load('backup');
            $mailer = new Mailer();
            $mailer->prepMessage([
                'title' => Locale::phrase(['string' => '<no-reply> %s - updates for %s.', 'vars' => [CLUB_NAME, date('d.m.Y')]]),
                'body' => "<p>Database changes.</p><p>Here is some changes in DB related to a finish of day actions:</p><p>" . implode("</p><p>", $_SESSION['report']) . "</p>",
            ]);
            $mailer->send($settings['email']['value']);
        }
    }
}
