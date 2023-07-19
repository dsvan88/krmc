<section class="section near-evening">
    <? /*<form class="booking" action="/days<?= $dayId ?>/w<?= $weekId ?>" data-wid="<?= $weekId ?>" data-did="<?= $dayId ?>">*/ ?>
    <form class="booking" action="/week/<?= $day['weekId'] ?>/day/<?= $day['dayId'] ?>/" method="POST">
        <h3 class="booking__title"><?= $texts['daysBlockTitle'] ?></h3>
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
                    <? /*<option value="mafia" <?= ($day['game'] === 'mafia' ? 'selected' : '') ?>><?= $texts['dayGameMafia'] ?></option>
                    <option value="poker" <?= ($day['game'] === 'poker' ? 'selected' : '') ?>><?= $texts['dayGamePoker'] ?></option>
                    <option value="board" <?= ($day['game'] === 'board' ? 'selected' : '') ?>><?= $texts['dayGameBoard'] ?></option>
                    <option value="cash" <?= ($day['game'] === 'cash' ? 'selected' : '') ?>><?= $texts['dayGameCash'] ?></option>
                    <option value="etc" <?= ($day['game'] === 'etc' ? 'selected' : '') ?>><?= $texts['dayGameEtc'] ?></option>*/ ?>
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
                    <input type="text" name="day_prim" value='<?= $day['day_prim'] ?>' placeholder="<?= $texts['dayRemarkPlaceHolder'] ?>" />
                </div>
            </div>
        </div>
        <div class="booking__participants">
            <h2 class="booking__subtitle"><?= $texts['daysBlockParticipantsTitle'] ?>:</h2>

            <? for ($x = 0; $x < $playersCount; $x++) :
                $userName = '';
                if (isset($day['participants'][$x]['name'])) {
                    if (strpos($day['participants'][$x]['name'], 'tmp_user') === false) {
                        $userName = $day['participants'][$x]['name'];
                    } else {
                        $userName = '+1';
                    }
                };
            ?>
                <div class="booking__participant">
                    <label for="booking__participant-<?= $x ?>" class="booking__participant-num"><?= ($x + 1) ?>.</label>
                    <div class="booking__participant-info">
                        <input name="participant[]" type="text" value="<?= $userName ?>" class="booking__participant-name" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" data-action-change="participant-check-change" />
                        <input name="arrive[]" list="time-list" type="text" class="booking__participant-arrive" value="<?= isset($day['participants'][$x]) ? $day['participants'][$x]['arrive'] : '' ?>" autocomplete="off" />
                        <input name="prim[]" type="text" class="booking__participant-prim" value="<?= isset($day['participants'][$x]) ? $day['participants'][$x]['prim'] : '' ?>" placeholder="<?= $texts['dayRemarkPlaceHolder'] ?>">
                        <i class="fa fa-minus-circle booking__participant-remove" data-action-click="participant-field-clear" title="<?= $texts['clearLabel'] ?>"></i>
                    </div>
                </div>
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