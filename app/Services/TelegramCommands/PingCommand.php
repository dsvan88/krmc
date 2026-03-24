<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Week;
use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Tech;
use app\models\Contacts;
use app\models\GameTypes;
use app\models\Weeks;
use app\Services\TelegramBotService;

class PingCommand extends ChatCommand
{
    public static $accessLevel = 'trusted';
    private static $weeksOffset = 4;
    private static $week = null;
    private static $day = null;

    public static function description()
    {
        return self::locale('<u>/ping (week day)</u> <i>// Ping users from current activity to get their attention.</i>');
    }
    public static function execute()
    {
        
        TelegramBotService::parseDayNum(static::$arguments[0] ?? 'tod');
        $weekId = Weeks::currentId();

        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        static::$week = Week::create($weekId);
        static::$day = static::$week->days[static::$arguments['dayNum']];
        
        if (static::$day->status !== 'set')
            return static::result('This day isn’t even started to find other participants.', '🤷‍♂');

        $userIds = static::findUserIds();

        if (empty($userIds))
            return static::result('Can’t find participants for this event.', '😢');

        $contacts = Contacts::findGroup('user_id', $userIds);
        $tgNames = [];
        foreach ($contacts as $contact) {
            if ($contact['type'] !== 'telegram') continue;
            $tgNames[] = $contact['contact'];
        }

        if (empty($tgNames)) {
            return static::result('Can’t find participants for this event.', '😢');
        }

        $format = 'd.m.Y ' . static::$day->time;
        $dayDate = strtotime(date($format, static::$day->timestamp));
        $date = date('d.m.Y', $dayDate);

        $game = static::$day->game;
        $gameName = static::$day->gameName;

        $lang = Locale::$langCode;
        $proto = Tech::getRequestProtocol();
        $link = "<a href='$proto://{$_SERVER['SERVER_NAME']}/game/{$game}/?lang=$lang'>{$gameName}</a>";

        $list = '@' . implode(', @', $tgNames);

        $message =  self::locale(['string' => "Dear players: %s!\n%s at %s we’re going to play in %s!\nAre you in?😉", 'vars' => [$list, $date, static::$day->time, $link]]);
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                ]
            ]
        ];
    }
    private static function findUserIds(): array
    {
        $existsIds = array_column(static::$day->participants, 'id');

        $bookedIds = [];
        foreach (static::$week->days as $day) {
            if ($day->dayId == static::$arguments['dayNum'] || static::$day->game !== $day->game) continue;
            $bookedIds = array_merge($bookedIds, array_column($day->participants, 'id'));
        }

        $offset = self::$weeksOffset;
        do {
            $week = Week::create(static::$week->id - $offset);
            foreach ($week->days as $day) {
                if (static::$day->game !== $day->game) continue;
                $bookedIds = array_merge($bookedIds, array_column($day->participants, 'id'));
            }
        } while (--$offset > 0);

        if (empty($bookedIds))
            return [];

        $userIds = [];
        foreach ($bookedIds as $userId) {
            if (empty($userId) || !is_numeric($userId) || in_array($userId, $userIds, true) || in_array($userId, $existsIds, true)) continue;
            $userIds[] = $userId;
        }
        return $userIds;
    }
}
