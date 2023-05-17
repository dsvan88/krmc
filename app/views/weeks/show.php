<section id="week-list" class="section week-list">
    <h1 class="week__title section__title">
        <?= $texts['weeksBlockTitle'] ?>
    </h1>
    <h2 class="week__title section__subtitle">
        <? if ($prevWeek) : ?>
            <span><a class="week__title-link" href="/weeks/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a></span>
        <? else : ?>
            <span class="week__title-dummy"></span>
        <? endif; ?>
        <span><?= date('d.m', $weekData['start']) . ' - ' . date('d.m', $weekData['finish'] - 3600 * 5) ?></span>
        <? if ($nextWeek) : ?>
            <span>
                <a class="week__title-link" href="/weeks/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <span class="week__title-dummy">
                &lt;&nbsp;No Data&nbsp;&gt;
            </span>
        <? endif; ?>
    </h2>
    <div class="week__list">
        <?
        foreach($days as $dayNum=>$day) :
        ?>
            <div class="week__item <?= $day['class'] ?>" data-action-click="/week/<?= $weekId ?>/day/<?= $dayNum ?>/" data-mode="location">
                <h3 class="week__item-date"><a href="/week/<?= $weekId ?>/day/<?= $dayNum ?>/"><?= $day['date'] ?></a></h3>
                <h4 class="week__item-game"><?= $day['game'] ?></h4>
                <div class="week__item-praticipants">
                    <div class="day-participants__list">
                        <ol class="day-participants__list-column">
                        <?
                        for ($x = 0; $x < $day['playersCount']; $x++) :
                            if ($x !== 0 && $x % 5 === 0) : ?>
                                </ol>
                                <ol class="day-participants__list-column">
                            <? endif ?>
                            <li class="day-participants__list-item"><?= $day['participants'][$x]['name'] ?></li>
                        <? endfor ?>
                        </ol>
                    </div>
                </div>
            </div>
        <? endforeach; ?>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="week__links"><?= $paginator ?></div>
    <? endif; ?>
</section>