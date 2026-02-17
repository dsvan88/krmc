<section class="section games-list">
    <h1 class="games__title section__title">
        <?= $texts['BlockTitle'] ?>
    </h1>
    <h2 class="schelude__title section__subtitle">
        <?php if ($prevWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/rating/<?= $prevWeek['id'] ?>"><?= date('d.m', $prevWeek['start']) . ' - ' . date('d.m', $prevWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <?php else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <?php endif;?>
        <span><?= date('d.m', $week['start']) . ' - ' . date('d.m', $week['finish'] - 3600 * 5) ?></span>
        <?php if ($nextWeek) : ?>
            <span>
                <a class="schelude__title-link" href="/activity/rating/<?= $nextWeek['id'] ?>"><?= date('d.m', $nextWeek['start']) . ' - ' . date('d.m', $nextWeek['finish'] - 3600 * 5) ?></a>
            </span>
        <?php else : ?>
            <span class="schelude__title-dummy">&lt;&nbsp;No Data&nbsp;&gt;</span>
        <?php endif ?>
    </h2>
    <div class="games__rating">
        <table class="game__table">
            <thead>
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Гравець</th>
                    <th colspan="4">Перемоги</th>
                    <th rowspan="2">Загальний рейтинг</th>
                    <th rowspan="2">Кількість ігор</th>
                    <th rowspan="2">Кількість перемог</th>
                    <th rowspan="2">Коефіцієнт перемог</th>
                    <th colspan="4">Бали</th>
                </tr>
                <tr>
                    <th>Дон</th>
                    <th>Шериф</th>
                    <th>Мафія</th>
                    <th>Мешканець</th>
                    <th>Перемога</th>
                    <th>Кращій Хід</th>
                    <th>Доп+</th>
                    <th>Доп-</th>
                </tr>
            </thead>
            <tbody class="game__table-body">
                <?php foreach($rating as $id=>$player):?>
                    <tr>
                        <td><?=($id+1)?></td>
                        <td class="player__name">
                            <?=$player['name']?>
                        </td>
                        <td>
                            <?= empty($player['win']['don']) ? '-' : $player['win']['don'] ?>
                        </td>
                        <td>
                            <?= empty($player['win']['sherif']) ? '-' : $player['win']['sherif'] ?>
                        </td>
                        <td>
                            <?= empty($player['win']['mafia']) ? '-' : $player['win']['mafia'] ?>
                        </td>
                        <td>
                            <?= empty($player['win']['peace']) ? '-' : $player['win']['peace'] ?>
                        </td>
                        <td>
                            <?=$player['points'] ?><?php // Бали за перемогу*Коєфіцієнт перемог + (Кращій Хід + Доп бали (плюсові) + Доп бали (негайтивні)) ?>
                        </td>
                        <td>
                            <?= $player['games'] ?>
                        </td>
                        <td>
                            <?= empty($player['win']['all']) ? '-' : $player['win']['all'] ?>
                        </td>
                        <td>
                            <?= empty($player['win']['all']) ? '-' : round($player['win']['all']/$player['games'],2) ?>
                        </td>
                        <td>
                            <?= empty($player['pointTypes']['Winners']) ? '-' : $player['pointTypes']['Winners'] ?>
                        </td>
                        <td>
                            <?= empty($player['pointTypes']['BestMove']) ? '-' : $player['pointTypes']['BestMove'] ?>
                        </td>
                        <td>
                            <?= empty($player['pointTypes']['positive']) ? '-' : $player['pointTypes']['positive'] ?>
                        </td>
                        <td>
                            <?= empty($player['pointTypes']['negative']) ? '-' : $player['pointTypes']['negative'] ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</section>