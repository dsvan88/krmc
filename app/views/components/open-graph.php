<?php foreach ($og as $property => $content) : ?>
    <?php if (is_array($content)) :?>
        <?php foreach ($content as $key => $value) : ?>
            <meta property="<?=$property?>:<?=$key?>" content="<?=$value?>" >
        <?endforeach?>
    <?php else: ?>
        <meta property="og:<?=$property?>" content="<?=$content?>" >
    <?php endif ?>
<?php endforeach ?>