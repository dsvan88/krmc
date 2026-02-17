<section class="section">
    <header>
        <h1 class="title"><?= $texts['BlockTitle'] ?>:<?= $dashboard ?></h1>
        <h2 class="subtitle"><?= $texts['BlockSubTitle'] ?></h3>
    </header>
    <div class='content'>
        <ol class="game-types">
            <?php foreach ($games as $game): ?>
                <li class="game-types__item">
                <?php if (!empty($game['data']['logo'])): ?>
                    <a class="game-types__logo" href="/game/<?= $game['slug'] ?>">
                        <img src="<?=$game['data']['logo']?>" alt="<?=$game['data']['logo']?>">
                    </a>
                <?php endif ?>
                <div class="game-types__details">
                    <h3 class="game-types__title"><a href="/game/<?= $game['slug'] ?>"><?= $game['title'] ?></a></h3>
                    <p class="game-types__description">
                        <?=$game['description']?>
                    </p>
                    <a class="game-types__readmore" href="/game/<?= $game['slug'] ?>">Read more</a>
                </div>
            </li>
            <?php endforeach ?>
        </ol>
    </div>
</section>