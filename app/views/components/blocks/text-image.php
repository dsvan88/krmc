<div class="block">
    <div class="block__content ti <?= $block['direction']  ?? ''?> <?= $block['order']  ?? ''?>">
        <div class="block__text">
            <h3 class="block__title"><?= $block['title']  ?? ''?></h3>
            <?= $block['html']  ?? ''?>
        </div>
        <div class="block__image"><img src="<?= $block['imageLink']  ?? ''?>" alt="" srcset=""></div>
    </div>
</div>