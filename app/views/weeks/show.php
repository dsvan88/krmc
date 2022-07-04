<?php

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;

?>
<section id="week-list" class="section week-list">
    <h2 class="week-preview__title section__title"><?= $texts['weeksBlockTitle'] . ' ' . date('d.m.Y', $weekData['start']) . ' ' . date('d.m.Y', $weekData['finish']) ?></h2>
    <div class="week-preview__list">
        <?
        for ($i = 0; $i < 7; $i++) :
            if (!isset($weekData['data'][$i])) {
                $weekData['data'][$i] = Days::$dayDataDefault;
            } else {
                foreach ($defaultDayData as $key => $value) {
                    if (!isset($weekData['data'][$i][$key])) {
                        $weekData['data'][$i][$key] = $value;
                    }
                }
            }

            $dayTimestamp = $monday + TIMESTAMP_DAY * $i;
            $dayDate = date('d.m.Y', $dayTimestamp) . ' (<strong>' . $texts['days'][$i] . '</strong>) ' . $weekData['data'][$i]['time'];
            $dayPlateClass = 'day-future';
            if ($selectedWeekIndex < $weekCurrentIndexInList) {
                $dayPlateClass = 'day-expire';
            } elseif ($selectedWeekIndex === $weekCurrentIndexInList) {
                if ($dayCurrentId > $i) {
                    $dayPlateClass = 'day-expire';
                } elseif ($dayCurrentId === $i) {
                    $dayPlateClass = 'day-current';
                }
            }

        ?>
            <div class="week-preview__item <?= $dayPlateClass ?>" data-action-click="/days<?= $i ?>/w<?= $weekId ?>" data-week="<?= $weekId ?>" data-day="<?= $dayId ?>" data-mode="location">
                <h4 class="week-preview__item-date"><?= $dayDate ?></h4>
                <h3 class="week-preview__item-game"><?= $texts['games'][$weekData['data'][$i]['game']] ?></h3>
                <div class="week-preview__item-praticipants">
                    <ol class="day-participants__list">
                        <div class="day-participants__list-column">
                            <?
                            $maxParticipantsCount = min(count($weekData['data'][$i]['participants']), 10);
                            for ($x = 0; $x < $maxParticipantsCount; $x++) :
                                $userName = '';
                                if (isset($weekData['data'][$i]['participants'][$x])) {
                                    if (strpos($weekData['data'][$i]['participants'][$x]['name'], 'tmp_user') !== false) {
                                        $userName = '+1';
                                    } else {
                                        $userName = $weekData['data'][$i]['participants'][$x]['name'];
                                    }
                                }
                                if ($x !== 0 && $x % 5 === 0) : ?>
                        </div>
                        <div class="day-participants__list-column">
                        <? endif ?>
                        <li class="day-participants__list-item"><?= $userName ?></li>
                    <? endfor ?>
                        </div>
                    </ol>
                </div>
            </div>
        <? endfor; ?>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="week-preview__links"><?= $paginator ?></div>
    <? endif; ?>
</section>