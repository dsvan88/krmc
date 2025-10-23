<section class="section">
    <div class="booking">
        <form class="booking__form" action="/week/<?= $day['weekId'] ?>/day/<?= $day['dayId'] ?>/" method="POST">
            <header class="booking__header">
                <? if (empty($yesterday['link'])) : ?>
                    <span class="booking__header-link"><?= $yesterday['label'] ?></span>
                <? else : ?>
                    <span class="booking__header-link"><a href="<?= $yesterday['link'] ?>"><i class="fa fa-angle-double-left"></i>&nbsp;<?= $yesterday['label'] ?></a></span>
                <? endif ?>
                <h3 class="booking__title"><?= $day['dateTime'] ?></h3>
                <? if (empty($tomorrow['link'])) : ?>
                    <span class="booking__header-link"><?= $tomorrow['label'] ?></span>
                <? else : ?>
                    <span class="booking__header-link"><a href="<?= $tomorrow['link'] ?>"><?= $tomorrow['label'] ?>&nbsp;<i class="fa fa-angle-double-right"></i></a></span>
                <? endif ?>
            </header>
            <div class="booking__body">
                <fieldset class="booking__settings">
                    <legend><?= $texts['daySettingsLegend'] ?>:</legend>
                    <div class="booking__row">
                        <label for="day-time" class="booking__label"> <?= $texts['dayStartTime'] ?>: </label>
                        <div class="booking__value">
                            <input list="time-list" type="text" name="day_time" value="<?= $day['time'] ?>" placeholder="<?= $texts['dayGameStart'] ?>" id="day-time" />
                        </div>
                    </div>
                    <div class="booking__row">
                        <label for="day-game" class="booking__label"><?= $texts['dayEvent'] ?>:</label>
                        <div class="booking__value">
                            <select name="game" id="day-game">
                                <? foreach ($gameTypes as $num => $gameType) : ?>
                                    <option value="<?= $gameType['slug'] ?>" <?= ($day['game'] === $gameType['slug'] ? 'selected' : '') ?>><?= $gameType['name'] ?></option>
                                <? endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="booking__row">
                        <label for="day-game" class="booking__label"><?= $texts['dayMods'] ?>:</label>
                        <?
                        self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'id' => 'beginners',
                            'name' => 'mods[]',
                            'value' => 'beginners',
                            'icon' => 'fa-graduation-cap',
                            'checked' => $day['beginners'],
                            'title' => 'Навчальна'
                        ]);
                        self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'id' => 'night',
                            'name' => 'mods[]',
                            'value' => 'night',
                            'icon' => 'fa-moon-o',
                            'checked' => $day['night'],
                            'title' => 'Нічна'
                        ]);
                        self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'id' => 'theme',
                            'name' => 'mods[]',
                            'value' => 'theme',
                            'icon' => 'fa-birthday-cake',
                            'checked' => $day['theme'],
                            'title' => 'Тематична'
                        ]);
                        self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'id' => 'funs',
                            'name' => 'mods[]',
                            'value' => 'funs',
                            'icon' => 'fa-child',
                            'checked' => $day['funs'],
                            'title' => 'Фанова'
                        ]);
                        self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'id' => 'tournament',
                            'name' => 'mods[]',
                            'value' => 'tournament',
                            'icon' => 'fa-trophy',
                            'checked' => $day['tournament'],
                            'title' => $texts['dayTournamentCheckboxLabel']
                        ]);
                        ?>
                    </div>
                    <div class="booking__row">
                        <label for="day-game" class="booking__label"><?= $texts['dayCosts'] ?>:</label>
                        <div class="booking__value">
                            <input type="text" name="day_cost" value="<?= $day['cost'] ?>" placeholder="<?= $texts['dayCosts'] ?>" id="day-time" />
                        </div>
                    </div>
                    <div class="booking__row">
                        <div class="booking__value">
                            <textarea
                                name="day_prim"
                                placeholder="<?= $texts['RemarkPlaceHolder'] ?>"><?= $day['day_prim'] ?></textarea>
                        </div>
                    </div>
                    <div class="booking__row submit">
                        <button type="submit" class="positive fa fa-save"></button>
                        <? self::component('forms/checkbox-icon', [
                            'prefix' => 'game',
                            'name' => 'send',
                            'value' => '1',
                            'icon' => 'fa-paper-plane-o',
                            'title' => $texts['daySendCheckboxLabel']
                        ]) ?>
                        <button type="reset" class="fa fa-undo"></button>
                    </div>
                </fieldset>
                <fieldset class="booking__participants">
                    <legend><?= $texts['daysBlockParticipantsTitle'] ?>:</legend>
                    <? for ($x = 0; $x < $playersCount; $x++)
                        self::component('participants-field', ['participantId' => $x, 'participant' => empty($day['participants'][$x]) ? [] : $day['participants'][$x]])
                    ?>
                    <button class="fa fa-plus cicrle" data-action-click="participant-field-get"></button>
                </fieldset>
                <div class="booking__buttons">
                    <button type="submit" class="positive fa fa-save"></button>
                </div>
            </div>
        </form>
    </div>
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