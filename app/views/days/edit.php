<section class="section near-evening">
    <? /*<form class="booking" action="/days<?= $dayId ?>/w<?= $weekId ?>" data-wid="<?= $weekId ?>" data-did="<?= $dayId ?>">*/ ?>
    <form class="booking" action="/week/<?= $day['weekId'] ?>/day/<?= $day['dayId'] ?>/" method="POST">
        <header class="booking__header">
            <? if (empty($yesterday['link'])) : ?>
                <span class="booking__header-link"><?= $yesterday['label'] ?></span>
            <? else : ?>
                <span class="booking__header-link"><a href="<?= $yesterday['link'] ?>"><i class="fa fa-angle-double-left"></i>&nbsp;<?= $yesterday['label'] ?></a></span>
            <? endif ?>
            <h3 class="booking__title"><?= $texts['daysBlockTitle'] ?></h3>
            <? if (empty($tomorrow['link'])) : ?>
                <span class="booking__header-link"><?= $tomorrow['label'] ?></span>
            <? else : ?>
                <span class="booking__header-link"><a href="<?= $tomorrow['link'] ?>"><?= $tomorrow['label'] ?>&nbsp;<i class="fa fa-angle-double-right"></i></a></span>
            <? endif ?>
        </header>
        <div class="booking__settings">
            <div class="booking__settings-row">
                <label for="game-day-time" class="booking__label-centered"> <?= $day['date'] ?> </label>
                <div class="booking__settings-wrapper">
                    <input list="time-list" type="text" name="day_time" value="<?= $day['time'] ?>" placeholder="<?= $texts['dayGameStart'] ?>" id="game-day-time" />
                </div>
            </div>
            <div class="booking__settings-row single">
                <select name="game">
                    <? foreach ($gameTypes as $num => $gameType) : ?>
                        <option value="<?= $gameType['slug'] ?>" <?= ($day['game'] === $gameType['slug'] ? 'selected' : '') ?>><?= $gameType['name'] ?></option>
                    <? endforeach ?>
                </select>
                <span class="checkbox-styled">
                    <input type="checkbox" name="mods[]" id="game-tournament-checkbox" value="tournament" class="checkbox-styled-checkbox" <?= $day['tournament'] ?> />
                    <label for="game-tournament-checkbox" class="checkbox-styled__label"> <?= $texts['dayTournamentCheckboxLabel'] ?> </label>
                </span>
                <span class="checkbox-styled">
                    <input type="checkbox" name="send" id="game-send-checkbox" value="1" class="checkbox-styled-checkbox" />
                    <label for="game-send-checkbox" class="checkbox-styled__label"> <?= $texts['daySendCheckboxLabel'] ?> </label>
                </span>
            </div>
            <div class="booking__settings-row">
                <div class="booking__settings-wrapper single">
                    <input type="text" name="day_prim" value='<?= $day['day_prim'] ?>' placeholder="<?= $texts['RemarkPlaceHolder'] ?>" />
                </div>
            </div>
        </div>
        <div class="booking__participants">
            <h2 class="booking__subtitle"><?= $texts['daysBlockParticipantsTitle'] ?>:</h2>
            <? for ($x = 0; $x < $playersCount; $x++) : ?>
                <? self::component('participants-field', ['participantId' => $x, 'participant' => empty($day['participants'][$x]) ? [] : $day['participants'][$x]]) ?>
            <? endfor ?>

        </div>

        <div class="booking__buttons">
            <button type="button" data-action-click="participant-field-get"><?= $texts['addFieldLabel'] ?></button>
        </div>
        <div class="booking__buttons">
            <button type="submit" class="positive"><?= $texts['setDayApprovedLabel'] ?></button>
        </div>
    </form>
    <datalist id="users-names-list"> </datalist>
    <datalist id="time-list">
        <?
        $min = (int) substr($day['time'], 0, 2);
        for (; $min < 23; $min++) : ?>
            <option value="<?= $min ?>:00"></option>
            <option value="<?= $min ?>:30"></option>
        <? endfor ?>
    </datalist>
</section>