<section class="section near-evening">
    <form class="booking">
        <h3 class="booking__title"><?= $texts['daysBlockTitle'] ?></h3>
        <div class="booking__day-settings">
            <div class="booking__day-settings-row">
                <h2 class="booking__day-event"><?= $texts['dayGame' . ucfirst($day['game'])] ?></h2>
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
                        <? if (isset($day['participants'][$x])) :
                            $userName = '';
                            if (isset($day['participants'][$x]['name'])) {
                                if (strpos($day['participants'][$x]['name'], 'tmp_user') === false) {
                                    $userName = $day['participants'][$x]['name'];
                                } else {
                                    $userName = '+1';
                                }
                            };
                        ?>
                            <span class="booking__participant-name"><?= $userName ?></span>
                            <span><?= $day['participants'][$x]['arrive'] ?></span>
                            <span><?= !empty($day['participants'][$x]['prim']) ? " (<em>{$day['participants'][$x]['prim']}</em>)" : '' ?></span>
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