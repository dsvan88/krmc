<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Tech;
use app\core\Telegram\ChatAnswer;
use app\core\TelegramBot;
use app\mappers\Coupons;
use app\Services\TelegramBotService;

class SendDiceAnswer extends ChatAnswer
{
    public static $accessLevel = 'user';
    public static $winNumbers = [6];

    public static function execute(): array
    {
        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $day = Day::create($dayNum, $weekId);
        
        if ($day->isExpired() || in_array($day->status, ['', 'recalled'])) {
            return static::result('This day is over🤷‍♂️', false);
        }
        
        $winners = static::getWinnersCount($day);
        
        if ($day->sales['winners'] >= $winners) {
            return static::result('There is no free slots for new winners.', false);
        }

        if (!empty($day->sales['results'][static::$requester->userId])){
            return static::result([
                    'string' => "You're used your chance.\nYour result is <i>%s</i>",
                    'vars' => [$day->sales['results'][static::$requester->userId]]
                ], false);
        }

        $tgBot = new TelegramBot();

        $tgBot->sendDice(TelegramBotService::getChatId());
        $value = $tgBot::$result['result']['dice']['value'];

        $day->sales['results'][static::$requester->userId] = $value;

        if (in_array($value, static::$winNumbers, false)){
            Coupons::create
        }
        return array_merge(static::result('Success', true), []);
    }
    private static function getWinnersCount(?Day $day = null){
        $r = 0;
        foreach($day->sales['results'] as $v){
            if (!in_array($v, static::$winNumbers, false)) continue;
            ++$r;
        }
        return $r;
    }
}