<section class="section">
    <header>
        <h1 class="title"><?= $texts['BlockTitle'] ?>:<?= $dashboard ?></h1>
        <h2 class="subtitle"><?= $texts['BlockSubTitle'] ?></h3>
    </header>
    <div class='content'>
        <ol>
            <? foreach ($games as $game => $name): ?>
                <li><a href="/game/<?= $game ?>"><?= $name ?></a></li>
            <? endforeach ?>
        </ol>
    </div>
</section>