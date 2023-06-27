<section class="section index">
  <?/*  <header>
        <h1 class="title"><?=$texts['title']?></h1>
        <h2 class="subtitle">
            <?=$texts['subtitle']?>
        </h2>
    </header>*/?>
    <form class="game-form" action="/game/mafia/start" method="POST">
        <div class="game-form__row">
            <input name="manager" type="text" class="game-form__input" value="" placeholder="<?=$texts['managerPlaceholder']?>"/>
        </div>
        <ol class="game-form__players-list">
            <? for($i=0; $i < $maxPlayers; $i++): ?>
                <li>
                    <div class="game-form__row">
                        <input name="player[<?=$i?>]" type="text" class="game-form__input" value ="<?=isset($day['participants'][$i]['name']) ? $day['participants'][$i]['name'] : ''?>" placeholder="<?=$texts['playerPlaceholder']?>" data-action-change="check-player" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
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
            if ($i < $maxPlayers)
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