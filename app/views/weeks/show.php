<section id="week-list" class="section week-list">
    <header>
        <h1 class="week__title title">
            <?= $texts['weeksBlockTitle'] ?>
        </h1>
        <h2 class="schelude__title section__subtitle">
            <?php if ($prevWeek) : ?>
                <span>
                    <a class="schelude__title-link" href="/weeks/<?= $prevWeek['id'] ?>/"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 18000) ?></a>
                </span>
            <?php else : ?>
                <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
            <?php endif ?>
            <span><?= date('d.m', $weekData['start']) . ' - ' . date('d.m', $weekData['finish'] - 18000) ?></span>
            <?php if ($nextWeek) : ?>
                <span>
                    <a class="schelude__title-link" href="/weeks/<?= $nextWeek['id'] ?>/"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 18000) ?></a>
                </span>
            <?php else : ?>
                <?php if ($isManager) : ?>
                    <span>
                        <a class="schelude__title-link" href="/weeks/add/">&lt;&nbsp;<i class="fa fa-plus-circle"></i>&nbsp;&gt;</a>
                    </span>
                <?php else : ?>
                    <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
                <?php endif ?>
            <?php endif ?>
        </h2>
    </header>
    <div class='content'>
        <div class="week__list">
            <?php foreach ($days as $dayNum => $day) : ?>
                <?php self::component('day-card', ['day' => $day, 'dayNum' => $dayNum, 'weekId' => $weekId]) ?>
            <?php endforeach ?>
        </div>
    </div>
    <?php if ($weeksCount > 1) : ?>
        <div class="paginator">
            <div class="paginator__links"><?= $paginator ?></div>
        </div>
    <?php endif ?>
</section>