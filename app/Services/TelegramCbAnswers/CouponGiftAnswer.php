<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\User;
use app\core\Telegram\ChatAnswer;
use app\Formatters\TelegramBotFormatter;
use app\mappers\Coupons;
use app\mappers\Users;
use Exception;

class CouponGiftAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';
    public static ?User $target = null;

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

        static::$target = User::create($userId);

        if (empty(static::$target))
            return static::result('User not found');

        if ($goods === 'coupon')
            return static::coupons($cId);

        return static::result('Success', false);
    }
    public static function coupons(int $cId)
    {
        $coupons = Coupons::getTypes();

        if (empty($coupons[$cId])) {
            return static::result('This coupon is not found');
        }
        // $price = $coupons[$cId]['price'];
        $discount = $coupons[$cId]['options']['discount'] . $coupons[$cId]['options']['discount_type'];

        // if ($price > SocialPoints::get(static::$target->id))
        //     return array_merge(static::result('You’re don’t have enough Social Points', false, true));

        $code = Coupons::create(static::$target->id, $cId, 'ready');

        // if ($code) SocialPoints::minus(static::$target->id, $price);

        $message = self::locale(['string' => 'You’re successfully presented the coupon #%s (discount - %s), as a gift to the user %s.', 'vars' => [$code, $discount, static::$target->name]]);

        $result = static::couponsMenu();

        $result['message'] = $message;
        
        return $result;
    }
    public static function couponsMenu()
    {
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotFormatter::getCouponsListGiftMarkup(static::$target->id);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];

        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true, true), ['update' => [$update]]);
    }
}
