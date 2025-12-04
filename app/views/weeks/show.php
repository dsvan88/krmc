<section id="week-list" class="section week-list">
    <header>
        <h1 class="week__title title">
            <?= $texts['weeksBlockTitle'] ?>
        </h1>
        <h2 class="schelude__title section__subtitle">
            <? if ($prevWeek) : ?>
                <span>
                    <a class="schelude__title-link" href="/weeks/<?= $prevWeek['id'] ?>/"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 18000) ?></a>
                </span>
            <? else : ?>
                <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
            <? endif ?>
            <span><?= date('d.m', $weekData['start']) . ' - ' . date('d.m', $weekData['finish'] - 18000) ?></span>
            <? if ($nextWeek) : ?>
                <span>
                    <a class="schelude__title-link" href="/weeks/<?= $nextWeek['id'] ?>/"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 18000) ?></a>
                </span>
            <? else : ?>
                <? if ($isManager) : ?>
                    <span>
                        <a class="schelude__title-link" href="/weeks/add/">&lt;&nbsp;<i class="fa fa-plus-circle"></i>&nbsp;&gt;</a>
                    </span>
                <? else : ?>
                    <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
                <? endif ?>
            <? endif ?>
        </h2>
    </header>
    <div class='content'>
        <div class="week__list">
            <? foreach ($days as $dayNum => $day) : ?>
                <? self::component('day-card', ['day' => $day, 'dayNum' => $dayNum, 'weekId' => $weekId]) ?>
            <? endforeach ?>
        </div>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="paginator">
            <div class="paginator__links"><?= $paginator ?></div>
        </div>
    <? endif ?>
</section>