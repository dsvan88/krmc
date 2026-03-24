<?php

namespace app\Formatters;

use app\core\Entities\Day;
use app\models\Weeks;

class WeekFormatter
{
    public static function yesterday(?Day $day): array
    {
        if (empty($day))
            return [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];

        if ($day->dayId == 0 && !Weeks::checkPrevWeek($day->weekId)) {
            return [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];
        }

        return [
            'link' => $day->dayId > 0 ? "/week/{$day->weekId}/day/" . ($day->dayId - 1) . '/' : '/week/' . ($day->weekId - 1) . '/day/6/',
            'label' => date('d.m', $day->timestamp - TIMESTAMP_DAY),
        ];
    }
    public static function tomorrow(?Day $day): array
    {
        if (empty($day))
            return [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];

        if ($day->dayId == 6 && !Weeks::checkNextWeek($day->weekId)) {
            return [
                'link' => '',
                'label' => '&lt; No Data &gt;',
            ];
        }
        return [
            'link' => $day->dayId < 6 ? "/week/{$day->weekId}/day/" . ($day->dayId + 1) . '/' : '/week/' . ($day->weekId + 1) . '/day/0/',
            'label' => date('d.m', $day->timestamp + TIMESTAMP_DAY),
        ];
    }
}
