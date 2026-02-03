<section class="section games-list">
    <h1 class="games__title section__title">
        <?= $texts['BlockTitle'] ?>
    </h1>
    <h2 class="schelude__title section__subtitle">
        <?php if ($prevWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/history/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <?php else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <?php endif;?>
        <span><?= date('d.m', $week['start']) . ' - ' . date('d.m', $week['finish'] - 3600 * 5) ?></span>
        <?php if ($nextWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/history/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <?php else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <?php endif ?>
    </h2>
    <div class="games__list">
        <?php foreach ($games as $game) : ?>
            <?php self::component('game-history-item',['game' => $game, 'week' => $week, 'teams' => $teams]) ?>
        <?php endforeach; ?>
    </div>
    <?php if ($weeksCount > 1) : ?>
        <div class="paginator">
            <div class="paginator__links"><?= $paginator ?></div>
        </div>
    <?php endif; ?>
</section>