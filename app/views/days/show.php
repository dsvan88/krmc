<section class="section day">
    <form class="day__form">
        <header class="day__header">
            <? if (empty($yesterday['link'])) : ?>
                <span class="day__navlink"><?= $yesterday['label'] ?></span>
            <? else : ?>
                <span class="day__navlink"><a href="<?= $yesterday['link'] ?>"><i class="fa fa-angle-double-left"></i>&nbsp;<?= $yesterday['label'] ?></a></span>
            <? endif ?>
            <h3 class="day__title"><?= $day['dateTime'] ?></h3>
            <? if (empty($tomorrow['link'])) : ?>
                <span class="day__navlink"><?= $tomorrow['label'] ?></span>
            <? else : ?>
                <span class="day__navlink"><a href="<?= $tomorrow['link'] ?>"><?= $tomorrow['label'] ?>&nbsp;<i class="fa fa-angle-double-right"></i></a></span>
            <? endif ?>
        </header>
        <div class="day__body">
            <h2 class="day__event"><a href="/game/<?= $day['game'] ?>/"><?= $day['gameName'] ?></a></h2>
            <div class="day__settings">
                <div class="day__prim"><?= $day['day_mods_text'] ?></div>
                <div class="day__prim"><?= $day['day_prim'] ?></div>
                <div class="day__row">
                    <label class="day__label"><?= $texts['dayStartTime'] ?>:</label> <span><u><?= $day['dateTime'] ?></u></span>
                </div>
                <div class="day__row">
                    <label class="day__label"><?= $texts['dayCosts'] ?>:</label> <span><u><?= $day['cost'] ?></u></span>
                </div>
            </div>
            <div class="day__participants">
                <h2 class="day__subtitle"><?= $texts['daysBlockParticipantsTitle'] ?></h2>
                <? for ($x = 0; $x < $playersCount; $x++) : ?>
                    <div class="participant">
                        <label class="participant__num"><?= ($x + 1) ?>.</label>
                        <div class="participant__info">
                            <? if (isset($day['participants'][$x])) : ?>
                                <div class="participant__name"><?= empty($day['participants'][$x]['name']) ? '' : $day['participants'][$x]['name'] ?></div>
                                <div class="participant__details">
                                    <span><?= empty($day['participants'][$x]['arrive']) ? '' : $day['participants'][$x]['arrive'] ?></span>
                                    <span><?= empty($day['participants'][$x]['prim']) ? '' : " (<em>{$day['participants'][$x]['prim']}</em>)" ?></span>
                                </div>
                            <? else : ?>
                                <div class="participant__name"></div>
                                <div class="participant__details">
                                    <span></span>
                                    <span></span>
                                </div>
                            <? endif ?>
                        </div>
                    </div>
                <? endfor ?>
                <? if (!empty($selfBooking)) : ?>
                    <div class="day__self">
                        <a href="<?= $selfBooking['link'] ?>" class="button"><?= $selfBooking['label'] ?></a>
                    </div>
                <? endif; ?>
            </div>
        </div>
    </form>
</section>