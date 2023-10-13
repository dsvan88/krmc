<section class="section index">
    <form class="game-form" action="/game/mafia/start" method="POST">
        <details class="game-form__row spaced">
            <summary><?= $texts['gameSettingsTitle'] ?></summary>
            <fieldset>
                <legend>Основні:</legend>
                <div class="game-form__row">
                    <label for="vote-type" class="game-form__label">Тип голосування:</label>
                    <select name="vote-type" id="vote-type" class="game-form__input">
                        <option value="count" <?= $config['voteType'] === 'count' ? 'selected' : ''?>>Кількість</option>
                        <option value="enum" <?= $config['voteType'] === 'enum' ? 'selected' : ''?>>Список</option>
                    </select>
                </div>
                <div class="game-form__row">
                    <label for="court-after-fouls" class="game-form__label">Суд після 4-го фола:</label>
                    <select name="court-after-fouls" id="court-after-fouls" class="game-form__input">
                        <option value="1" <?= $config['courtAfterFouls'] ? 'selected' : ''?>>Так</option>
                        <option value="0" <?= $config['courtAfterFouls'] ? '' : 'selected'?>>Ні</option>
                    </select>
                </div>
            </fieldset>
            <fieldset>
                <legend>Максимальна кількість гравців за столом:</legend>
                <div class="game-form__row">
                    <label for="get-out-half-players-min" class="game-form__label">Для підняття половини гравців:</label>
                    <input type="number" name="get-out-half-players-min" id="get-out-half-players-min" value="<?= $config['getOutHalfPlayersMin']?>">
                </div>
                <div class="game-form__row">
                    <label for="muted-speak-max-count" class="game-form__label">Для промови мовчазних:</label>
                    <input type="number" name="muted-speak-max-count" id="muted-speak-max-count" value="<?= $config['mutedSpeakMaxCount']?>">
                </div>
                <div class="game-form__row">
                    <label for="best-move-players-min" class="game-form__label">Для залишення КХ:</label>
                    <input type="number" name="best-move-players-min" id="best-move-players-min" value="<?= $config['bestMovePlayersMin']?>">
                </div>
            </fieldset>
            <fieldset>
                <legend>Тривалість:</legend>
                <div class="game-form__row">
                    <label for="timer-max" class="game-form__label">Час промови:</label>
                    <input type="number" name="timer-max" id="timer-max" value="<?= $config['timerMax']?>" step="500">
                </div>
                <div class="game-form__row">
                    <label for="last-will-time" class="game-form__label">Час заповіту:</label>
                    <input type="number" name="last-will-time" id="last-will-time" value="<?= $config['lastWillTime']?>" step="500">
                </div>
                <div class="game-form__row">
                    <label for="debate-time" class="game-form__label">Час дебатів:</label>
                    <input type="number" name="debate-time" id="debate-time" value="<?= $config['debateTime']?>" step="500">
                </div>
                <div class="game-form__row">
                    <label for="muted-speak-time" class="game-form__label">Час мовчазного гравця:</label>
                    <input type="number" name="muted-speak-time" id="muted-speak-time" value="<?= $config['mutedSpeakTime']?>" step="500">
                </div>
                <div class="game-form__row">
                    <label for="wake-up-roles" class="game-form__label">Час шерифа:</label>
                    <input type="number" name="wake-up-roles" id="wake-up-roles" value="<?= $config['wakeUpRoles']?>" step="500">
                </div>
            </fieldset>
            <fieldset>
                <legend>Нарахування балів:</legend>
                <div class="game-form__row">
                    <label for="points[winner]" class="game-form__label">Перемога:</label>
                    <input type="number" step="0.1" name="points[winner]" id="points[winner]" value="<?= $config['points']['winner']?>">
                </div>
                <div class="game-form__row">
                    <label for="points[sherifFirstStaticKill]" class="game-form__label">Статичне вбивство Шерифа:</label>
                    <input type="number" step="0.1" name="points[sherifFirstStaticKill]" id="points[sherifFirstStaticKill]" value="<?= $config['points']['sherifFirstStaticKill']?>">
                </div>
                <div class="game-form__row">
                    <label for="points[sherifFirstDynamicKill]" class="game-form__label">Динамічне вбивство Шерифа:</label>
                    <input type="number" step="0.1" name="points[sherifFirstDynamicKill]" id="points[sherifFirstDynamicKill]" value="<?= $config['points']['sherifFirstDynamicKill']?>">
                </div>
                <div class="game-form__row">
                    <label for="points[bestMove]" class="game-form__label">Кращій хід:</label>
                    <input type="text" name="points[bestMove]" id="points[bestMove]" value="<?= implode(', ',$config['points']['bestMove'])?>">
                </div>
                <div class="game-form__row">
                    <label for="points[aliveMafs]" class="game-form__label">Живим мафіозі:</label>
                    <input type="text" name="points[aliveMafs]" id="points[aliveMafs]" value="<?= implode(', ',$config['points']['aliveMafs'])?>">
                </div>
                <div class="game-form__row">
                    <label for="points[aliveReds]" class="game-form__label">Живим мирним:</label>
                    <input type="text" name="points[aliveReds]" id="points[aliveReds]" value="<?= implode(', ',$config['points']['aliveReds'])?>">
                </div>
            </fieldset>
            <fieldset>
                <legend>Штрафи:</legend>
                <div class="game-form__row">
                    <label for="points[fourFouls]" class="game-form__label">4-й фолл:</label>
                    <input type="number" step="0.1" name="points[fourFouls]" id="points[fourFouls]" value="<?= $config['points']['fourFouls']?>">
                </div>
                <div class="game-form__row">
                    <label for="points[disqualified]" class="game-form__label">Дискваліфікація:</label>
                    <input type="number" step="0.1" name="points[disqualified]" id="points[disqualified]" value="<?= $config['points']['disqualified']?>">
                </div>
                <div class="game-form__row">
                    <label for="points[voteInSherif]" class="game-form__label">Голос в Шерифа при 9х:</label>
                    <input type="number" step="0.1" name="points[voteInSherif]" id="points[voteInSherif]" value="<?= $config['points']['voteInSherif']?>">
                </div>
            </fieldset>
            <fieldset>
                <legend>Безпека:</legend>
                <div class="game-form__row">
                    <label for="game-pass" class="game-form__label">PIN-код на гру:</label>
                    <input type="text" name="game-pass" id="game-pass" value="<?= $config['gamePass'] ?>">
                </div>
            </fieldset>
            <fieldset>
                <legend>Налаштування за замовчуванням:</legend>
                <div class="game-form__row">
                    <span class="checkbox-styled">
                        <input type="checkbox" name="default" id="save-as-default-checkbox" value="1" class="checkbox-styled-checkbox" />
                        <label for="save-as-default-checkbox" class="checkbox-styled__label">Сохранить</label>
                    </span>
                </div>
            </fieldset>
        </details>
        <div class="game-form__row spaced">
            <button class="fa fa-random" data-action-click="players-shuffle"></button>
            <input name="manager" type="text" class="game-form__input" value="<?= $manager ?>" placeholder="<?= $texts['managerPlaceholder'] ?>" />
            <button class="fa fa-eraser" data-action-click="players-clear"></button>
        </div>
        <ol class="game-form__players-list">
            <? for ($i = 0; $i < $maxPlayers; $i++) :
                $playerName = '';
                if (!empty($shuffled))
                    $playerName = array_shift($shuffled);
            ?>
                <li>
                    <div class="game-form__row">
                        <input name="player[<?= $i ?>]" type="text" class="game-form__input" value="<?= $playerName ?>" placeholder="<?= $texts['playerPlaceholder'] ?>" data-action-change="check-player" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
                    </div>
                </li>
            <? endfor ?>
        </ol>
        <div class="game-form__row">
            <button type="submit" class="game-form__button"><?= $texts['Start'] ?></button>
        </div>
    </form>
    <div class="game-form__pool">
        <? for ($i = 0; $i < $playersCount; $i++) :
            $class = [];
            if ($day['participants'][$i]['name'] === $manager)
                $class[] = 'manager';
            else if (!in_array($day['participants'][$i]['name'], $shuffled)){
                $class[] = $day['participants'][$i]['name'] === '+1' ? 'dummy-player' : 'selected';
            }
        ?>
            <span class="game-form__pool-unit">
                <span class="game-form__pool-name <?= implode(' ', $class) ?>" data-action-click="toggle-player"><?= $day['participants'][$i]['name'] ?></span>
                <span class="game-form__pool-remove fa fa-times" data-action-click="remove-participant"></span>
            </span>
        <? endfor ?>
        <span class="game-form__pool-unit add" data-action-click="add/participant/form">
            <span class="fa fa-plus"><?= $texts['addPlayer'] ?></span>
        </span>
    </div>
    <datalist id="users-names-list"></datalist>
</section>