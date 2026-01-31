<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;
use app\core\Tech;
use app\core\Validator;
use app\Repositories\AccountRepository;
use app\Repositories\DayRepository;
use app\Repositories\TelegramChatsRepository;

class Days extends Model
{
    public static $table = SQL_TBL_WEEKS;
    public static $currentDay;

    public static $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];
    public static $dayDataDefault = [
        'game' => 'mafia',
        'mods' => [],
        'time' => '14:00',
        'status' => '',
        'participants' => [],
        'day_prim' => ''
    ];

    public static function current(): int
    {
        if (!empty(self::$currentDay)) {
            return self::$currentDay;
        }

        self::$currentDay = getdate()['wday'] - 1;

        if (self::$currentDay === -1)
            self::$currentDay = 6;

        return self::$currentDay;
    }
    public static function near()
    {
        $weekId = Weeks::currentId();
        $dayId = self::current();
        $dayData = self::weekDayData($weekId, $dayId);

        if ($dayData['status'] === 'set') return [$weekId, $dayId];

        return DayRepository::findNearSetDay($weekId, $dayId);
    }
    public static function isExpired(int $timestamp): bool
    {
        return $timestamp + TIMESTAMP_DAY < $_SERVER['REQUEST_TIME'] - 3600;
    }
    public static function edit($weekId, $dayId, $data)
    {
        $newData = [
            'time' => trim($data['day_time']),
            'game' => trim($data['game']),
            'day_prim' => str_replace('  ', "\n", trim($data['day_prim'])),
            'status' => 'set',
            'mods' => [],
            'cost' => trim($data['day_cost']),
        ];
        if (isset($data['mods'])) {
            $newData['mods'] = $data['mods'];
        }
        $newData['participants'] = [];
        $count = count($data['participant']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($data['participant'][$i])) continue;
            if ($data['participant'][$i] === '+1') {
                $id = null;
            } elseif ($data['participant'][$i][0] === '@') {
                $tgName = substr($data['participant'][$i], 1);
                $chatData = TelegramChats::findByUserName($tgName);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['uid'];
            } elseif ($data['participant'][$i][0] === '_') {
                $tgChatId = substr($data['participant'][$i], 1);
                $chatData = TelegramChats::getChat($tgChatId);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['uid'];
            } else {
                $name = $data['participant'][$i];
                $id = Users::getId($name);
                if ($id < 2) {
                    $name = Validator::validate('name', $name);

                    if (empty($name)) continue;

                    $id = Users::getId($name);
                    if ($id < 2) {
                        $id = Users::add($name);
                    }
                }
            }

            $newData['participants'][] = [
                'id' => $id,
                'arrive' => trim($data['arrive'][$i]),
                'prim' => trim($data['prim'][$i]),
            ];
        }
        return self::setDayData($weekId, $dayId, $newData);
    }
    public static function setStatus(int $weekId, int $dayNum, string $status = 'set')
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] === $status) {
            return false;
        }
        $weekData['data'][$dayNum]['status'] = $status;
        return self::update(['data' => json_encode($weekData, JSON_UNESCAPED_UNICODE)], ['id' => $weekId]);
    }
    public static function setDayData($weekId, $dayId, $data)
    {
        try {
            if (empty($weekId))
                $weekId = Weeks::currentId();

            if ($weekId > 0) {
                $weekData = Weeks::weekDataById($weekId);
            } else {
                $weekData = Weeks::defaultWeekData();
                $weekData['start'] = strtotime('last Monday');
                $weekData['finish'] = strtotime('next Sunday');
            }

            unset($weekData['id']);

            $weekData['data'][$dayId] = $data;
            $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);

            if ($weekId > 0) {
                self::update($weekData, ['id' => $weekId]);
                return $weekId;
            } else {
                return self::insert($weekData);
            }
        } catch (\Throwable $th) {
            error_log(__METHOD__ . $th->__toString());
            return false;
        }
    }
    public static function weekDayData($weekId, $dayId)
    {
        $weekData = Weeks::weekDataById($weekId);

        if (!$weekData) return self::$dayDataDefault;

        $weekData['data'][$dayId]['weekStart'] = $weekData['start'];

        if (empty($weekData['data'][$dayId]['participants'])) return $weekData['data'][$dayId];

        AccountRepository::addNames($weekData['data'][$dayId]['participants']);
        $count = count($weekData['data'][$dayId]['participants']);
        for ($x = 0; $x < $count; $x++) {
            if (!empty($weekData['data'][$dayId]['participants'][$x]['id'])) continue;
            $weekData['data'][$dayId]['participants'][$x]['name'] = '+1';
        }

        return $weekData['data'][$dayId];
    }
    public static function getFullDescription($weekData, $day)
    {
        $dayTimestamp = $weekData['start'] + (TIMESTAMP_DAY * $day);
        $format = 'd.m.Y ' . $weekData['data'][$day]['time'];
        $dayDate = strtotime(date($format, $dayTimestamp));

        $game = $weekData['data'][$day]['game'];

        if ($_SERVER['REQUEST_TIME'] > $dayDate + DATE_MARGE || in_array($weekData['data'][$day]['status'], ['', 'recalled'])) {
            return '';
        }

        $result = '🗓 - <u>' . date('d.m.Y', $dayDate) . ' (<b>' . Locale::phrase(self::$days[$day]) . '</b>)</u>' . PHP_EOL;
        $result .= DayRepository::getTimeEmoji($weekData['data'][$day]['time']) . ' - <u>' . $weekData['data'][$day]['time'] . '</u>' . PHP_EOL;

        $gameNames = [
            'mafia' => '{{ Tg_Mafia }}',
            'board' => '{{ Tg_Board }}',
            'nlh' => '{{ Tg_NLH }}',
            'etc' => '{{ Tg_Etc }}',
        ];
        $gameNames = Locale::apply($gameNames);

        if (empty($gameNames[$game])) {
            $gameNames = GameTypes::names();
        }

        $lang = Locale::$langCode;
        $proto = Tech::getRequestProtocol();
        $result .= "🎮 - <a href='$proto://{$_SERVER['SERVER_NAME']}/game/{$weekData['data'][$day]['game']}/?lang=$lang'>{$gameNames[$weekData['data'][$day]['game']]}</a>\n";

        $result .= DayRepository::getModsTexts($weekData['data'][$day]['mods']);

        if (!empty($weekData['data'][$day]['cost']))
            $result .= "💲 - <u>{$weekData['data'][$day]['cost']}</u>\n";
        if (!empty($weekData['data'][$day]['day_prim']))
            $result .= "<u>{$weekData['data'][$day]['day_prim']}</u>\n";

        $contacts = Settings::get('contacts');
        $place = mb_substr($contacts['adress']['value'], mb_strrpos($contacts['adress']['value'], '  ', 0, 'UTF-8') + 2, null, 'UTF-8');

        $result .= "📍 - <a href='{$contacts['gmap_link']['value']}'>$place</a>\n";
        $result .= "\n";

        $participants = [];
        $participantsToEnd = [];
        $noNames = [];

        AccountRepository::addNames($weekData['data'][$day]['participants']);
        $count = count($weekData['data'][$day]['participants']);
        for ($x = 0; $x < $count; $x++) {
            if (!is_numeric($weekData['data'][$day]['participants'][$x]['id'])) {
                $noNames[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            if (empty($weekData['data'][$day]['participants'][$x]['name']))
                continue;
            if (!empty($weekData['data'][$day]['participants'][$x]['prim']) || !empty($weekData['data'][$day]['participants'][$x]['arrive'])) {
                $participantsToEnd[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            $participants[] = $weekData['data'][$day]['participants'][$x];
        }
        $participants = array_merge($participants, $participantsToEnd, $noNames);

        $count = count($participants);
        for ($x = 0; $x < $count; $x++) {
            $modsParts = [];
            $userName = '+1';

            if (!empty($participants[$x]['name'])) {
                $userName = $participants[$x]['name'];
                if (!empty($participants[$x]['emoji'])) {
                    $userName .= $participants[$x]['emoji'];
                }
                if (!empty($participants[$x]['status']) && !empty($participants[$x]['gender'])) {
                    $userName .= Users::$accessTgEmoji[$participants[$x]['status']][$participants[$x]['gender']];
                }
            }

            if (!empty($participants[$x]['arrive']) && $participants[$x]['arrive'] !== $weekData['data'][$day]['time']) {
                $modsParts[] = DayRepository::getTimeEmoji($participants[$x]['arrive']) . ' ' . $participants[$x]['arrive'];
            }
            if ($userName[0] === '_') {
                $tgChat = TelegramChats::getChat(substr($userName, 1));
                $userName = '+1';
                $chatTitle = TelegramChatsRepository::chatTitle($tgChat);
                if (!empty($chatTitle)) {
                    $modsParts[] = $chatTitle.', ';
                }
            }
            if ($participants[$x]['prim'] != '') {
                $modsParts[] = $participants[$x]['prim'];
            }
            if (!empty($modsParts))
                $modsParts = ' (<i>'.implode(', ',$modsParts). '</i>)';
            $result .= ($x + 1) . ". <b>$userName</b>$modsParts\r\n";
        }
        return $result;
    }
    public static function removeParticipant(int $weekId, int $dayId, $userId): bool
    {
        $dayData = self::weekDayData($weekId, $dayId);
        $slot = -1;
        while (isset($dayData['participants'][++$slot])) {
            if ($dayData['participants'][$slot]['id'] != $userId) continue;

            unset($dayData['participants'][$slot]);
            $dayData['participants'] = array_values($dayData['participants']);

            return self::setDayData($weekId, $dayId, $dayData);
        }
        return false;
    }
    public static function addParticipant(int $weekId, int $dayId, int $userId): bool
    {
        $dayData = self::weekDayData($weekId, $dayId);
        self::addParticipantToDayData($dayData, ['userId' => $userId]);
        return self::setDayData($weekId, $dayId, $dayData);
    }
    public static function addParticipantToDayData(array &$dayData, array $userData, int $slot = -1): void
    {
        if ($slot === -1) {
            while (isset($dayData['participants'][++$slot])) {
            }
        }

        $dayData['participants'][$slot] = [
            'id'        =>    $userData['userId'],
            'arrive'    =>    empty($userData['arrive']) ? '' : $userData['arrive'],
            'prim'      =>    empty($userData['prim']) ? '' : $userData['prim'],
        ];
    }
    public static function addNonamesToDayData($dayData, $slot, $count, $prim = '')
    {
        if ($slot === -1) {
            while (isset($dayData['participants'][++$slot])) {
            }
        }

        for ($x = 0; $x < $count; $x++) {
            $dayData['participants'][$slot + $x] = [
                'id'        =>    null,
                'arrive'    =>    '',
                'prim'    =>     $prim,
            ];
        }
        return $dayData;
    }
    public static function removeNonamesFromDayData($dayData, $count)
    {
        $count = (int) $count;
        $newParticipants = [];
        for ($x = 0; $x < count($dayData['participants']); $x++) {
            if (!empty($dayData['participants'][$x]['id']) || $count <= 0) {
                $newParticipants[] = $dayData['participants'][$x];
                continue;
            }
            --$count;
        }
        $dayData['participants'] = $newParticipants;

        return $dayData;
    }
    public static function recall($weekId, $dayNum)
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] === 'recalled') {
            return false;
        }
        $weekData['data'][$dayNum]['status'] = 'recalled';
        return self::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);
    }
    public static function clear($weekId, $dayNum)
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] !== 'recalled') {
            return false;
        }
        $weekData['data'][$dayNum]['day_prim'] = '';
        $weekData['data'][$dayNum]['participants'] = [];

        return self::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);
    }
}
