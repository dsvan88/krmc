<section id="week-list" class="section week-list">
    <h1 class="week__title section__title">
        <?= $texts['weeksBlockTitle'] ?>
    </h1>
    <h2 class="schelude__title section__subtitle">
        <? if ($prevWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/weeks/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <? endif ?>
        <span><?= date('d.m', $weekData['start']) . ' - ' . date('d.m', $weekData['finish'] - 3600 * 5) ?></span>
        <? if ($nextWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/weeks/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <? if ($isManager) : ?>
                <span>
                    <a class="schelude__title-link" href="/weeks/add">&lt;&nbsp;<i class="fa fa-plus-circle"></i>&nbsp;&gt;</a>
                </span>
            <? else : ?>
                <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
            <? endif ?>
        <? endif ?>
    </h2>
    <div class="week__list">
        <? foreach ($days as $dayNum => $day) : ?>
            <? self::component('day-card', ['day' => $day, 'dayNum' => $dayNum, 'weekId' => $weekId]) ?>
        <? endforeach ?>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="paginator__links"><?= $paginator ?></div>
    <? endif ?>
</section>