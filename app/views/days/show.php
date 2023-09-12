<section class="section near-evening">
    <form class="booking">
        <header class="booking__header">
            <span class="booking__header-link"><a href="<?= $yesterday['link'] ?>"><i class="fa fa-angle-double-left"></i>&nbsp;<?= $yesterday['label'] ?></a></span>
            <h3 class="booking__title"><?= $texts['daysBlockTitle'] ?></h3>
            <span class="booking__header-link"><a href="<?= $tomorrow['link'] ?>"><?= $tomorrow['label'] ?>&nbsp;<i class="fa fa-angle-double-right"></i></a></span>
        </header>
        <div class="booking__day-settings">
            <div class="booking__day-settings-row">
                <h2 class="booking__day-event"><?= $gameName ?></h2>
            </div>
            <div class="booking__day-settings-row">
                <h4 class="booking__day-prim"><?= $day['day_prim'] ?></h4>
            </div>
            <div class="booking__day-settings-row">
                <label class="booking__day-settings-label"><?= $texts['dayStartTime'] ?></label> <span><?= $day['date'] ?></span>
            </div>
        </div>
        <div class="booking__participants">
            <h2 class="booking__subtitle"><?= $texts['daysBlockParticipantsTitle'] ?></h2>
            <? for ($x = 0; $x < $playersCount; $x++) : ?>
                <div class="booking__participant">
                    <label class="booking__participant-num"><?= ($x + 1) ?>.</label>
                    <div class="booking__participant-info">
                        <? if (isset($day['participants'][$x])) : ?>
                            <span class="booking__participant-name"><?= empty($day['participants'][$x]['name']) ? '' : $day['participants'][$x]['name'] ?></span>
                            <span><?= empty($day['participants'][$x]['arrive']) ? '' : $day['participants'][$x]['arrive'] ?></span>
                            <span><?= empty($day['participants'][$x]['prim']) ? '' : " (<em>{$day['participants'][$x]['prim']}</em>)" ?></span>
                        <? else : ?>
                            <span class="booking__participant-name"></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        <? endif ?>
                    </div>
                </div>
            <? endfor ?>
        </div>
    </form>
</section>