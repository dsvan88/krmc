<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Coupons;
use app\models\Days;
use app\models\SocialPoints;
use app\Repositories\TelegramBotRepository;
use Exception;

class UnregAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty.');

        $goods = trim(static::$arguments['g']);
        $cId = (int) trim(static::$arguments['i']);
        $userId = (int) trim(static::$arguments['u']);

        if (static::$requester->profile->id != $userId)
            return static::result('You don’t have enough rights to change information about other users!');

        if ($goods === 'coupon')
            return static::coupons($userId, $cId);

        return static::result('Success', false);
    }
    public static function coupons(int $userId, int $cId)
    {
        $price = Coupons::$coupons[$cId]['price'];
        $discount = Coupons::$coupons[$cId]['options']['discount'] . Coupons::$coupons[$cId]['options']['discount_type'];

        if ($price > SocialPoints::get($userId))
            return array_merge(static::result('You’re don’t have enough Social Points', false, true));

        $code = Coupons::create($userId, $cId);

        if ($code) SocialPoints::minus($userId, $price);

        static::$report = self::locale(['string' => 'User <b>%s</b> is successfully bought a coupon #%s (discount - %s) from the shop.', 'vars' => [static::$requester->profile->name, $code, $discount]]);

        return static::couponsMenu();
    }
    public static function couponsMenu()
    {
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotRepository::getCouponsListMarkup(false);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];

        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true, true), ['update' => [$update]]);
    }
}
