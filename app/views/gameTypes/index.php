<section class="section index">
    <header class="title">
        <h1>Ігри нашого клуба:<?=$dashboard?></h1>
    </header>
    <h3 class="subtitle">Наш клуб дозвілля збирається для ігор у такі ігри:</h3>
    <ol>
        <?foreach($games as $game=>$name):?>
            <li><a href="/game/<?=$game?>"><?=$name?></a></li>
        <?endforeach?>
    </ol>
</section>