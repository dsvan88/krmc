<?php

namespace app\Formatters;

use app\core\Entities\Day;
use app\core\Locale;
use app\core\Tech;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Services\CouponService;
use app\Services\DayService;
use app\Services\TelegramBotService;
use app\Services\TelegramChatsService;

class DayFormatter
{
    public static function dayDescription(?Day $day = null): string
    {
        if (empty($day)) return false;
        $result = "$day->date - {$day->gameName}\n" . Locale::phrase('Already registered players') . ": $day->participantsCount\n";
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function getTimeEmoji(string $time = ''): string
    {
        if (empty($time)) return '';

        $offset = strpos($time, ':');
        $hour = (int) substr($time, 0, $offset);
        if ($hour > 12) $hour -= 12;
        $mins = (int) substr($time, $offset + 1);
        if (empty($mins) || $mins > 0 && $mins <= 15) $mins = '';
        elseif ($mins > 15 && $mins <= 45) $mins = 30;
        elseif ($mins > 45 && $mins <= 59) {
            ++$hour;
            $mins = '';
        } elseif ($mins >= 60) {
            $hour += round($mins / 60);
            $mins = '';
        }
        $clocks = [
            '1' => '🕐',
            '130' => '🕜',
            '2' => '🕑',
            '230' => '🕝',
            '3' => '🕒',
            '330' => '🕞',
            '4' => '🕓',
            '430' => '🕟',
            '5' => '🕔',
            '530' => '🕠',
            '6' => '🕕',
            '630' => '🕡',
            '7' => '🕖',
            '730' => '🕢',
            '8' => '🕗',
            '830' => '🕣',
            '9' => '🕘',
            '930' => '🕤',
            '10' => '🕙',
            '1030' => '🕥',
            '11' => '🕚',
            '1130' => '🕦',
            '12' => '🕛',
            '1230' => '🕧',
        ];
        return isset($clocks[$hour . $mins]) ? $clocks[$hour . $mins] : '';
    }
    public static function forMessengers(?Day $day = null): string
    {
        if (empty($day)) return '';

        $format = 'd.m.Y ' . $day->time;
        $dayDate = strtotime(date($format, $day->timestamp));

        if ($_SERVER['REQUEST_TIME'] > $dayDate + DATE_MARGE || in_array($day->status, ['', 'recalled'])) {
            return '';
        }

        $result = '🗓 - <u>' . date('d.m.Y', $dayDate) . ' (<b>' . $day->dayName . '</b>)</u>' . PHP_EOL;
        $result .= static::getTimeEmoji($day->time) . ' - <u>' . $day->time . '</u>' . PHP_EOL;

        $gameNames = [
            'mafia' => '{{ Tg_Mafia }}',
            'board' => '{{ Tg_Board }}',
            'nlh' => '{{ Tg_NLH }}',
            'etc' => '{{ Tg_Etc }}',
        ];
        $gameNames = Locale::apply($gameNames);

        $gameName = $gameNames[$day->game] ?? $day->gameName;

        $lang = Locale::$langCode;
        $proto = Tech::getRequestProtocol();
        $result .= "🎮 - <a href='$proto://{$_SERVER['SERVER_NAME']}/game/{$day->game}/?lang=$lang'>{$gameName}</a>\n";

        $result .= DayService::getModsTexts($day->mods);

        if (!empty($day->cost))
            $result .= "💲 - <u>{$day->cost}</u>\n";
        if (!empty($day->day_prim))
            $result .= "🗒 - <u>{$day->day_prim}</u>\n";

        $contacts = Settings::get('contacts');
        $place = mb_substr($contacts['adress']['value'], mb_strrpos($contacts['adress']['value'], '  ', 0, 'UTF-8') + 2, null, 'UTF-8');

        $result .= "📍 - <a href='{$contacts['gmap_link']['value']}'>$place</a>\n";
        $result .= "\n";

        if (isset($day->coupons[0])){
            CouponService::getDayCoupons($day);
        }

        $participants = $participantsToEnd = $noNames = [];
        foreach ($day->participants as $participant) {
            if (!is_numeric($participant['id'])) {
                $noNames[] = $participant;
                continue;
            }
            if (empty($participant['name']))
                continue;
            if (!empty($participant['prim']) || !empty($participant['arrive'])) {
                $participantsToEnd[] = $participant;
                continue;
            }
            $participants[] = $participant;
        }
        $participants = array_merge($participants, $participantsToEnd, $noNames);

        foreach ($participants as $i => $participant) {
            $modsParts = [];
            $userName = '+1';

            if (!empty($participant['name'])) {
                $userName = '';
                if (!empty($participant['status']) && !empty($participant['gender'])) {
                    $userName = Users::$accessTgEmoji[$participant['status']][$participant['gender']];
                }
                $userName .= $participant['name'];
                if (!empty($participant['emoji'])) {
                    $userName .= $participant['emoji'];
                }
                if (isset($day->coupons[$participant['id']]) && TelegramBotService::getMessageId() === Settings::getAdminChatTelegramId()){
                    $userName .=  "💲- <i><u>{$day->coupons[$participant['id']]['options']['discount']}{$day->coupons[$participant['id']]['options']['discount_type']}</u></i>";
                }
            }

            if (!empty($participant['arrive']) && $participant['arrive'] !== $day->time) {
                $modsParts[] = static::getTimeEmoji($participant['arrive']) . ' ' . $participant['arrive'];
            }
            if ($userName[0] === '_') {
                $tgChat = TelegramChats::find(substr($userName, 1));
                $userName = '+1';
                $chatTitle = TelegramChatsService::chatTitle($tgChat);
                if (!empty($chatTitle)) {
                    $modsParts[] = $chatTitle;
                }
            }
            if ($participant['prim'] != '') {
                $modsParts[] = $participant['prim'];
            }

            $modsParts = empty($modsParts) ? '' : ' (<i>' . implode(', ', $modsParts) . '</i>)';
            $result .= ($i + 1) . ". <b>$userName</b>$modsParts\r\n";
        }
        return $result;
    }
}
