<section class="section">
    <header>
        <h1 class="title">Ігри нашого клуба:<?=$dashboard?></h1>
        <h2 class="subtitle">Наш клуб дозвілля збирається для ігор у такі ігри:</h3>
    </header>
    <ol>
        <?foreach($games as $game=>$name):?>
            <li><a href="/game/<?=$game?>"><?=$name?></a></li>
        <?endforeach?>
    </ol>
</section>