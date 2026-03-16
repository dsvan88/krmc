<?php

namespace app\Repositories;

use app\models\SocialPoints;
use app\models\Weeks;
use app\models\Days;

class SocialPointsRepository
{
    public static function applyBookingPoints(int $weekId = 0): void
    {

        if (empty($weekId)) {
            $weekId = Weeks::currentId();

            if (!Weeks::checkPrevWeek($weekId)) return;

            --$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        foreach ($weekData['data'] as $num => $day) {

            if ($day['status'] !== 'set') continue;

            $count = count($day['participants']);

            if ($day['game'] === 'mafia' && $count < 11 || $count < 6) continue;

            $starter = $day['starter'] ?? 0;

            foreach ($day['participants'] as $participant) {

                if (!is_numeric($participant['id'])) continue;

                $points = empty($participant['prim']) || strpos($participant['prim'], '?') === false ? SocialPoints::$points['booking'] : SocialPoints::$points['unsureBooking'];

                if ($participant['id'] == $starter) $points += SocialPoints::$points['dayStarter'];

                try {
                    SocialPoints::add($participant['id'], $points);
                } catch (\Throwable $e) {
                    $_SESSION['debug'][] = $participant['id'] . ' error -> ' . $e->getMessage();
                };
            }
            Days::setStatus($weekId, $num, 'finished');
        }
    }
    public static function evaluateMessage(int $userId = 0, string $message = ''): void
    {
        if (empty($userId) || empty($message)) return;

        $message = str_replace('  ', ' ', $message);
        if (substr_count($message, ' ') < 50) return;

        SocialPoints::add($userId, SocialPoints::$points['longMessage']);
    }
}
