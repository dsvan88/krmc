<section class="section index">
    <form class="game-form" action="/game/mafia/start" method="POST">
        <details class="game-form__row spaced">
            <summary><?=$texts['gameSettingsTitle']?></summary>
            <!--             getOutHalfPlayersMin: 4,
        killsPerNight: 1,
        timerMax: 6000,
        lastWillTime: 6000,
        debateTime: 3000,
        mutedSpeakTime: 3000,
        courtAfterFouls: true,
        wakeUpRoles: 2000,
        mutedSpeakMaxCount: 5,
        voteType: 'enum', // 'count'
        points: {
            winner: 1.0,
            bestMove: [0.0, 0.0, 0.25, 0.4],
            aliveMafs: [0.0, 0.3, 0.15, 0.3],
            aliveReds: [0.0, 0.0, 0.15, 0.1],
            disqualified: -0.3,
            sherifFirstStaticKill: 0.3,
            sherifFirstDynamicKill: 0.3,
        } -->
            <div class="game-form__row">
                <label for="vote-type">Тип голосування:</label>
                <select name="vote-type" id="vote-type">
                    <option value="enum" selected>Лист</option>
                    <option value="count">Кількість</option>
                </select>
            </div>
            <div class="game-form__row">
                Тривалість:
            </div>
            <div class="game-form__row">
                <label for="timer-max">Час промови:</label>
                <input type="number" name="timer-max" value="6000">
            </div>
            <div class="game-form__row">
                <label for="last-will-time">Час заповіту:</label>
                <input type="number" name="last-will-time" id="last-will-time" value="6000">
            </div>
            <div class="game-form__row">
                <label for="debate-time">Час дебатів:</label>
                <input type="number" name="debate-time" id="debate-time" value="3000">
            </div>
            <div class="game-form__row">
                <label for="muted-speak-time">Час мовчазного гравця:</label>
                <input type="number" name="muted-speak-time" id="muted-speak-time" value="3000">
            </div>
            <div class="game-form__row">
                <label for="wake-up-roles">Час шерифа:</label>
                <input type="number" name="wake-up-roles" id="wake-up-roles" value="2000">
            </div>
            <div class="game-form__row">
                Максимальна кількість гравців столом:
            </div>
            <div class="game-form__row">
                <label for="muted-speak-max-count">Для промови мовчазного гравця:</label>
                <input type="number" name="muted-speak-max-count" id="muted-speak-max-count" value="5">
            </div>
            <div class="game-form__row">
                <label for="get-out-half-players-min">Для підняття половини гравців:</label>
                <input type="number" name="get-out-half-players-min" id="get-out-half-players-min" value="4">
            </div>
            <div class="game-form__row">
                <label for="court-after-fouls">Проводити суд після 4-го фола у день:</label>
                <select name="court-after-fouls" id="court-after-fouls">
                    <option value="1">Так</option>
                    <option value="0" selected>Ні</option>
                </select>
            </div>
        </details>
        <div class="game-form__row spaced">
            <button class="fa fa-random" data-action-click="players-shuffle"></button>
            <input name="manager" type="text" class="game-form__input" value="<?= $manager ?>" placeholder="<?=$texts['managerPlaceholder']?>"/>
            <button class="fa fa-eraser" data-action-click="players-clear"></button>
        </div>
        <ol class="game-form__players-list">
            <? for($i=0; $i < $maxPlayers; $i++):
                $playerName= '';
                if (!empty($shuffled))
                    $playerName = array_shift($shuffled);

                if ($playerName === $manager){
                    ++$maxPlayers;
                    continue;
                }
                ?>
                <li>
                    <div class="game-form__row">
                        <input name="player[<?=$i?>]" type="text" class="game-form__input" value ="<?= $playerName ?>" placeholder="<?=$texts['playerPlaceholder']?>" data-action-change="check-player" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
                        <select name="role[<?=$i?>]" class="game-form__input">
                            <option value='0'> </option>
                            <option value='1'>Мафия</option>
                            <option value='2'>Дон мафии</option>
                            <option value='4'>Шериф</option>
                        </select>
                    </div>
                </li>
            <?endfor?>
        </ol>
        <div class="game-form__row">
            <button type="submit" class="game-form__button"><?=$texts['Start']?></button>
        </div>
    </form>
    <div class="game-form__pool">
        <? for($i=0; $i < $playersCount; $i++): 
            $class = [];
            if ($day['participants'][$i]['name'] === $manager)
                $class[] = 'manager';
            else if (!in_array($day['participants'][$i]['name'], $shuffled))
                $class[] = 'selected';
            if ($day['participants'][$i]['name'] === '+1')
                $class[] = 'dummy-player';
            ?>
            <span class="game-form__pool-unit">
                <span class="game-form__pool-name <?=implode(' ',$class)?>" data-action-click="toggle-player"><?=$day['participants'][$i]['name']?></span>
                <span class="game-form__pool-remove fa fa-times" data-action-click="remove-participant"></span>
            </span>
        <? endfor ?>
        <span class="game-form__pool-unit add" data-action-click="add/participant/form">
            <span class="fa fa-plus"><?=$texts['addPlayer']?></span>
        </span>
    </div>
    <datalist id="users-names-list"></datalist>
</section>