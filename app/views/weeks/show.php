<section id="week-list" class="section week-list">
    <h2 class="week__title section__title">
        <?= $texts['weeksBlockTitle'] ?>
    </h2>
    <h2 class="week__title section__subtitle">
        <? if ($prevWeek) : ?>
            <a class="week__title-link" href="/weeks/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a>
        <? else : ?>
            <span class="week__title-dummy"></span>
        <? endif; ?>
        <span><?= date('d.m', $weekData['start']) . ' - ' . date('d.m', $weekData['finish'] - 3600 * 5) ?></span>
        <? if ($nextWeek) : ?>
            <a class="week__title-link" href="/weeks/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
        <? else : ?>
            <span class="week__title-dummy">
                &lt;&nbsp;No Data&nbsp;&gt;
            </span>
        <? endif; ?>
    </h2>
    <div class="week__list">
        <?
        for ($i = 0; $i < 7; $i++) :
            if (!isset($weekData['data'][$i])) {
                $weekData['data'][$i] = $defaultDayData;
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
            <div class="week__item <?= $dayPlateClass ?>" data-action-click="/days<?= $i ?>/w<?= $weekId ?>" data-week="<?= $weekId ?>" data-day="<?= $dayId ?>" data-mode="location">
                <h4 class="week__item-date"><?= $dayDate ?></h4>
                <h3 class="week__item-game"><?= $texts['games'][$weekData['data'][$i]['game']] ?></h3>
                <div class="week__item-praticipants">
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
        <div class="week__links"><?= $paginator ?></div>
    <? endif; ?>
</section>