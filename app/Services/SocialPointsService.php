<?php

namespace app\Services;

use app\core\Entities\Week;
use app\models\SocialPoints;
use app\models\Weeks;
use app\models\Days;

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

            if ($day->status !== 'set') continue;

            if ($day->game === 'mafia' && $day->participantCounts < 11 || $day->participantCounts < 6) continue;

            $starter = $day->starter ?? 0;

            foreach ($day->participants as $participant) {

                if (!is_numeric($participant['id'])) continue;

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
        $week->save();
    }
    public static function evaluateMessage(int $userId = 0, string $message = ''): void
    {
        if (empty($userId) || empty($message)) return;

        $message = str_replace('  ', ' ', $message);
        if (substr_count($message, ' ') < 50) return;

        SocialPoints::add($userId, SocialPoints::$points['longMessage']);
    }
}
