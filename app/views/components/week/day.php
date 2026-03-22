<div class="day <?= $day->type ?>" data-action-click="/week/<?= $day->weekId ?>/day/<?= $day->dayId ?>/" data-mode="location">
    <h3 class="day__date"><a href="/week/<?= $day->weekId ?>/day/<?= $day->dayId ?>/"><?= $day->date ?></a></h3>
    <h4 class="day__game"><a href="/game/<?= $day->game ?>/"><?= $day->gameName ?></a></h4>
    <div class="day__participants">
        <!-- <div class="day__list"> -->
            <ol class="day__list-column">
                <?php
                for ($x = 0; $x < $day->participantsCount; $x++) :
                    if ($x !== 0 && $x % 5 === 0) : ?>
            </ol>
            <ol class="day__list-column" start="6">
                <?php endif ?>
                <li class="day__participant"><?= $day->participants[$x]['name'] ?></li>
                <?php endfor ?>
            </ol>
        <!-- </div> -->
    </div>
</div>