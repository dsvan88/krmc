<?php

namespace app\Formatters;

use app\core\Entities\Day;
use app\core\Entities\Week;
use app\core\Locale;
use app\core\Telegram\ChatAction;
use app\mappers\Coupons;
use app\mappers\Days;
use app\mappers\GameTypes;
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

        $requesterId = ChatAction::$requester->profile->id;

        $inline_keyboard = [];
        foreach ($days as $dayNum) {
            if ($dayNum < $curr) {
                $weekId++;
                $week = Weeks::create($weekId);
                if (empty($week)) break;
            }
            if (!$all && $week->days[$dayNum]->status !== 'set') continue;
            $inline_keyboard[] = [['text' => $week->days[$dayNum]->dayName . ' - ' . $week->days[$dayNum]->gameName, 'callback_data' => ['c' => $callback, 'w' => $weekId, 'd' => $dayNum, 'r' => $requesterId]]];
        }
        return compact('inline_keyboard');
    }
    public static function getGamesListMarkup(int $weekId = 0, int $dayId = 0): array
    {
        $games = GameTypes::menu();
        if (empty($games)) {
            return [];
        }

        $requesterId = ChatAction::$requester->profile->id;

        $inline_keyboard = [];
        foreach ($games as $game) {
            $inline_keyboard[] = [['text' => $game['name'], 'callback_data' => ['c' => 'set', 'w' => $weekId, 'd' => $dayId, 'p' => 'game', 'v' => $game['slug'], 'r' => $requesterId]]];
        }
        return compact('inline_keyboard');
    }
    public static function getPaticipantsListMarkup(string $callback = 'unreg', int $weekId = 0, int $dayId = 0): array
    {
        $day = Day::create($dayId, $weekId);
        if (empty($day->participants)) {
            return [];
        }

        $requesterId = ChatAction::$requester->profile->id;

        $inline_keyboard = [];
        foreach ($day->participants as $participant) {
            if (empty($participant['name'])) continue;
            $inline_keyboard[] = [['text' => $participant['name'], 'callback_data' => ['c' => $callback, 'w' => $weekId, 'd' => $dayId, 'u' => $participant['id'], 'r' => $requesterId]]];
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
    public static function getCouponsListBuyMarkup(bool $avail = false): array
    {
        $coupons = Coupons::getTypes();

        if (empty($coupons)) return ['inline_keyboard' => []];

        $userId = ChatAction::$requester->profile->id;
        $points = ChatAction::$requester->profile->points;
        $inline_keyboard = [];
        foreach ($coupons as $index => $coupon) {
            if ($avail && $points < $coupon['price']) continue;
            $inline_keyboard[] = [['text' => "{$coupon['icon']} - {$coupon['name']} ({$coupon['options']['discount']}{$coupon['options']['discount_type']}, {$coupon['price']}SP)", 'callback_data' => ['c' => 'spBuy', 'g' => 'coupon', 'i' => $index, 'u' => $userId]]];
        }
        return compact('inline_keyboard');
    }
    public static function getCouponsListGiftMarkup(int $userId, bool $avail = false): array
    {
        $coupons = Coupons::getTypes();

        if (empty($coupons)) return ['inline_keyboard' => []];

        $requesterId = ChatAction::$requester->profile->id;
        $points = ChatAction::$requester->profile->points;
        $inline_keyboard = [];
        foreach ($coupons as $index => $coupon) {
            if ($avail && $points < $coupon['price']) continue;
            $inline_keyboard[] = [['text' => "{$coupon['icon']} - {$coupon['name']} ({$coupon['options']['discount']}{$coupon['options']['discount_type']}, {$coupon['price']}SP)", 'callback_data' => ['c' => 'couponGift', 'g' => 'coupon', 'i' => $index, 'r' => $requesterId, 'u' => $userId]]];
        }
        return compact('inline_keyboard');
    }
}
