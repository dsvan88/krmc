<div class="day <?= $day['class'] ?>" data-action-click="/week/<?= $weekId ?>/day/<?= $dayNum ?>/" data-mode="location">
    <h3 class="day__date"><a href="/week/<?= $weekId ?>/day/<?= $dayNum ?>/"><?= $day['date'] ?></a></h3>
    <h4 class="day__game"><a href="/game/<?= $day['game'] ?>/"><?= $day['gameName'] ?></a></h4>
    <div class="day__participants">
        <!-- <div class="day__list"> -->
            <ol class="day__list-column">
                <?
                for ($x = 0; $x < $day['playersCount']; $x++) :
                    if ($x !== 0 && $x % 5 === 0) : ?>
            </ol>
            <ol class="day__list-column" start="6">
                <? endif ?>
                <li class="day__participant"><?= $day['participants'][$x]['name'] ?></li>
                <? endfor ?>
            </ol>
        <!-- </div> -->
    </div>
</div>