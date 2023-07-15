<section class="section index">
    <form class="game-form" action="/game/mafia/start" method="POST">
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