<section class="section games-list">
    <h1 class="games__title section__title">
        <?= $texts['BlockTitle'] ?>
    </h1>
    <h2 class="schelude__title section__subtitle">
        <? if ($prevWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/history/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <? endif;?>
        <span><?= date('d.m', $week['start']) . ' - ' . date('d.m', $week['finish'] - 3600 * 5) ?></span>
        <? if ($nextWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/history/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <? endif ?>
    </h2>
    <div class="games__list">
        <? foreach ($games as $game) : ?>
            <? self::component('game-history-item',['game' => $game, 'week' => $week, 'teams' => $teams]) ?>
        <? endforeach; ?>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="paginator">
            <div class="paginator__links"><?= $paginator ?></div>
        </div>
    <? endif; ?>
</section>