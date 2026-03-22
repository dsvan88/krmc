<?php

namespace app\Formatters;

use app\core\Locale;
use app\Repositories\TelegramBotRepository;

class TelegramBotFormatter
{
    public static function getBookingMarkup(int $weekId, int $dayNum, bool $booked = false, bool $full = false): array
    {
        if (TelegramBotRepository::isDirect() && $booked) {
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

        if ($full || !TelegramBotRepository::isDirect()) {
            $result['inline_keyboard'][0][] = ['text' => '❌' . Locale::phrase('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]];
        }

        return $result;
    }
}