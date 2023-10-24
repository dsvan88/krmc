<section class="section games-list">
    <h1 class="games__title section__title">
        <?= $texts['BlockTitle'] ?>
    </h1>
    <h2 class="games__title section__subtitle">
        <? /*if ($prevgames) : ?>
            <span>
                <a class="games__title-link" href="/games/<?= $prevgames['id'] ?>"><?= date('d.m', $prevgames['start']) . ' - ' . date('d.m', $prevgames['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <span class="games__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <? endif; */?>
        <span><?= date('d.m', $week['start']) . ' - ' . date('d.m', $week['finish'] - 3600 * 5) ?></span>
        <? /*if ($nextWeek) : ?>
            <span>
                <a class="games__title-link" href="/weeks/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <? else : ?>
            <? if ($isManager) : ?>
                <span>
                    <a class="games__title-link" href="/weeks/add">&lt;&nbsp;<i class="fa fa-plus-circle"></i>&nbsp;&gt;</a>
                </span>
            <? else : ?>
                <span class="games__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
            <? endif; ?>
        <? endif; */?>
    </h2>
    <div class="games__list">
        <? foreach ($games as $game) : ?>
            <? self::component('game-history-item',['game' => $game]) ?>
        <? endforeach; ?>
    </div>
    <? if ($weeksCount > 1) : ?>
        <div class="games__links"><?= $paginator ?></div>
    <? endif; ?>
</section>