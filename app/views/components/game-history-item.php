<details class="game-history__card" data-action-open="game-history-load" data-game-id="<?=$game['id']?>">
    <summary><?= date('d.m', $week['start'] + (TIMESTAMP_DAY * $game['day_id'])) ?> Winner: <?= empty($game['win']) ? 'In progress' : $game['win'] ?></summary>
    <div class="game-history__card">
        <i class="fa fa-cog fa-spin fa-3x fa-fw">
            <span class="sr-only">Завантаження...</span>
        </i>
    </div>
</details>