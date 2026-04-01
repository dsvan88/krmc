<?php

namespace app\Services;

use app\core\Entities\Day;
use app\core\Entities\Week;
use app\mappers\SocialPoints;
use app\mappers\Weeks;
use app\mappers\Days;

class SocialPointsService
{
    public static function applyBookingPoints(int $weekId = 0): void
    {

        if (empty($weekId)) {
            $weekId = Weeks::currentId();

            if (!Weeks::checkPrevWeek($weekId)) return;

            --$weekId;
        }

        $week = Week::create($weekId);

        foreach ($week->days as $day) {
            static::applyOnDay($day);
        }
        $week->save();
    }
    public static function applyOnDay(?Day $day): void
    {
        if ($day->status !== 'set') return;

        if ($day->game === 'mafia' && $day->participantsCount < 11 || $day->participantsCount < 6) {
            $day->status = 'recalled';
            return;
        }

        $starter = $day->starter ?? 0;

        foreach ($day->participants as $participant) {

            if (!is_numeric($participant['id'])) return;

            $points = empty($participant['prim']) || strpos($participant['prim'], '?') === false ? SocialPoints::$points['booking'] : SocialPoints::$points['unsureBooking'];

            if ($participant['id'] == $starter) $points += SocialPoints::$points['dayStarter'];

            try {
                SocialPoints::add($participant['id'], $points);
            } catch (\Throwable $e) {
                $_SESSION['debug'][] = $participant['id'] . ' error -> ' . $e->getMessage();
            };
        }
        $day->status = 'finished';
    }
    public static function evaluateMessage(int $userId = 0, string $message = ''): void
    {
        if (empty($userId) || empty($message)) return;

        $message = str_replace('  ', ' ', $message);
        if (substr_count($message, ' ') < 50) return;

        SocialPoints::add($userId, SocialPoints::$points['longMessage']);
    }
}
