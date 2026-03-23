<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use app\models\Days;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;
use Exception;

class BookingAnswer extends ChatAnswer
{
    private static ?Day $day = null;
    public static $text = '';
    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': arguments is empty');

        if (empty(static::$requester->profile)) {
            if (empty(static::$requester))
                return static::result('{{ Tg_Unknown_Requester }}', '🤷‍♂');
            static::$arguments['userId'] = '_' . static::$requester->id;
            static::$arguments['userName'] = empty(static::$requester->chat->username)
                ? static::$requester->chat->title . ' (<i>+1</i>)'
                : '@' . static::$requester->chat->username;
            static::$arguments['userStatus'] = 'all';
        } else {
            static::$arguments['userId'] = static::$requester->profile->id;
            static::$arguments['userName'] = static::$requester->profile->name;
            static::$arguments['userStatus'] = static::$requester->profile->status ?? 'user';
        }

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        static::$day = Day::create($dayNum, $weekId);

        if (Days::isExpired(static::$day->timestamp) || in_array(static::$day->status, ['', 'recalled'])) {
            return static::result('This day is over🤷‍♂️');
        }

        if (static::$day->status !== 'set') {
            if (!TelegramBotRepository::hasAccess(static::$arguments['userStatus'], 'trusted')) {
                return static::result('{{ Tg_Gameday_Not_Set }}');
            }
            static::$day->status = 'set';
        }

        $pIndex = -1;
        foreach (static::$day->participants as $index => $participant) {
            if ($participant['id'] != static::$arguments['userId']) continue;

            $pIndex = $index;
            break;
        }

        if ($pIndex === -1) {
            if (empty(static::$arguments['r']))
                static::addParticipant();
            else
                return static::result('{{ Tg_Command_Requester_Not_Booked }}');
        } else {
            if (empty(static::$arguments['r'])) {
                static::changePrim($pIndex);
            } else {
                static::removeParticipant($pIndex);
            }
        }

        static::$day->save();

        $booked = in_array(static::$requester->profile->id, array_column(static::$day->participants, 'id'));
        $replyMarkup = TelegramBotFormatter::getBookingMarkup($weekId, $dayNum, $booked);

        $update = [
            'message' => DayFormatter::forMessengers(static::$day),
            'replyMarkup' => $replyMarkup,
        ];

        $chatId = TelegramBotRepository::getChatId();
        if ($chatId != Settings::getMainTelegramId()) {
            static::$report .= PHP_EOL . static::locale(['string' => 'At Telegram chat: %s (%s).', 'vars' => [TelegramChats::getChatsTitle($chatId), $chatId]]);
        }
        return array_merge(static::result(static::$text, true, true), ['update' => [$update]]);
    }
    private static function addParticipant(array &$day = []): void
    {
        if (empty($day)) {
            throw new Exception(__METHOD__ . ': $day, cant be empty!');
        }
        $participant = [
            'userId' => static::$arguments['userId'],
            'prim' => static::$arguments['p'] ?? '',
        ];
        static::$day->addParticipant($participant);
        static::$text = static::locale(['string' => 'You’re successfully opted-in on a game %s at %s.', 'vars' => [static::$day->gameName, date('d.m.Y', static::$day->date)]]);
        static::$report = static::locale(['string' => 'User <b>%s</b> is opted-in on a game <b>%s</b> at <b>%s</b>.', 'vars' => [static::$arguments['userName'], static::$day->game, date('d.m.Y', static::$day->date)]]);
    }
    private static function changePrim(int $index = 0): void
    {
        if ($index < 0) {
            throw new Exception(__METHOD__ . ': $index or $participants, cant be empty!');
        }
        static::$day->participants[$index]['prim'] = static::$arguments['p'] ?? '';
        static::$text = static::locale('Success');
        static::$report = static::locale(['string' => 'User <b>%s</b> is changed prim on <b>%s</b>.', 'vars' => [static::$arguments['userName'], date('d.m.Y', static::$day->timestamp)]]);
    }
    private static function removeParticipant(int $index = 0): void
    {
        if ($index < 0 || empty($participants)) {
            throw new Exception(__METHOD__ . ': $index or $participants, cant be empty!');
        }
        static::$day->removeParticipant($index);
        static::$text = static::locale(['string' => 'You’re successfully opted-out from a game %s at %s.', 'vars' => [static::$day->gameName, static::$day->date]]);
        static::$report = static::locale(['string' => 'User <b>%s</b> is opted-out from a game <b>%s</b> at <b>%s</b>.', 'vars' => [static::$arguments['userName'], static::$day->gameName, static::$day->date]]);
    }
}
