<? foreach ($og as $property => $content) : ?>
    <? if (is_array($content)) :?>
        <? foreach ($content as $key => $value) : ?>
            <meta property="<?=$property?>:<?=$key?>" content="<?=$value?>" >
        <?endforeach?>
    <? else: ?>
        <meta property="og:<?=$property?>" content="<?=$content?>" >
    <? endif ?>
<? endforeach ?>