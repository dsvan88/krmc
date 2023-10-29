<details class="game-history__card" data-action-open="game-history-load" data-game-id="<?=$game['id']?>">
    <summary class="game-history__card-title <?=$game['class']?>">
        <span>
            <?= date('d.m', $week['start'] + (TIMESTAMP_DAY * $game['day_id'])) ?>
            <span>
                Id: <?= $game['id'] ?>
            </span>
        </span>
        
        <span class="game-history__card-result">
            Winner: <?= $teams[$game['win']] ?>
        </span>
    </summary>
    <div class="game-history__card">
        <i class="fa fa-cog fa-spin fa-3x fa-fw">
            <span class="sr-only">Завантаження...</span>
        </i>
    </div>
</details>