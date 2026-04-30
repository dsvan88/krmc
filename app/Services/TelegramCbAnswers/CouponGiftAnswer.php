<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\Formatters\TelegramBotFormatter;
use app\mappers\Coupons;
use Exception;

class CouponGiftAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty.');

        $goods = trim(static::$arguments['g']);
        $cId = (int) trim(static::$arguments['i']);
        $userId = (int) trim(static::$arguments['u']);
        $requesterId = (int) trim(static::$arguments['r']);

        if (static::$requester->profile->id != $requesterId)
            return static::result('You don’t have enough rights to change information about other users!');

        if ($goods === 'coupon')
            return static::coupons($userId, $cId);

        return static::result('Success', false);
    }
    public static function coupons(int $userId, int $cId)
    {
        $coupons = Coupons::getTypes();

        if (empty($coupons[$cId])) {
            return static::result('This coupon is not found');
        }
        // $price = $coupons[$cId]['price'];
        $discount = $coupons[$cId]['options']['discount'] . $coupons[$cId]['options']['discount_type'];

        // if ($price > SocialPoints::get($userId))
        //     return array_merge(static::result('You’re don’t have enough Social Points', false, true));

        $code = Coupons::create($userId, $cId, 'ready');
        

        // if ($code) SocialPoints::minus($userId, $price);

        static::$report = self::locale(['string' => 'You’re successfully present as gift a coupon #%s (discount - %s) to the user %s.', 'vars' => [$code, $discount]]);

        return static::couponsMenu();
    }
    public static function couponsMenu()
    {
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotFormatter::getCouponsListBuyMarkup();
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];

        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true, true), ['update' => [$update]]);
    }
}
