<div class="game__wrapper">
    <div class="game__description">
        <header class="title">
            <div class="game__stage"><?=$game['_stageDescr']?></div>
        </header>
    </div>
    <table class="game__table">
        <thead>
            <tr>
                <th>#</th>
                <th>Гравець</th>
                <th>Бали</th>
                <th colspan="<?=$game['daysCount']?>">Вист./Голос</th>
                <th>Фоли</th>
                <th>Примітка</th>
            </tr>
        </thead>
        <tbody class="game__table-body">
            <? foreach($game['players'] as $index=>$player):?>
                <?
                $class = ['player'];
                if (!empty($player['out']))
                    $class[] = 'out';
                if (isset($game['activeSpeaker']['id']) && $game['activeSpeaker']['id'] == $index)
                    $class[] = 'speaker';
                ?>
                <tr class="<?= empty($class) ? '' : implode(' ', $class) ?>" >
                    <td><?=($index+1)?></td>
                    <td class="player__name">
                        <?=$player['name']?>
                    </td>
                    <td><?=$player['points'] ?></td>
                    <? if ($game['daysCount'] > 0): ?>
                    <? for ($x=0; $x < $game['daysCount']; $x++) :?>
                        <td>
                            <?=$player['puted'][$x] < 0 ? '' : $player['puted'][$x]+1?>
                            /
                            <?=$player['voted'][$x] < 0 ? '' : substr($player['voted'][$x], 0, -2)?>
                        </td>
                    <? endfor ?>
                    <? else :?>
                        <td></td>
                    <? endif ?>
                    <td><?= (empty($player['fouls']) ? '' : $player['fouls'])?></td>
                    <td><?= (empty($player['_prim']) ? '' : $player['_prim'])?></td>
            <? endforeach ?>
        </tbody>
    </table>
    <details class="game__log">
        <summary>Log</summary>
    </details>
</div>