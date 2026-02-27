<?php
    $block['type'] = empty($block['order']) ? $block['type'] : 'image-text';
?>
<div class="block ti__wrapper" data-block-type="<?= $block['type'] ?>">
    <fieldset>
        <legend>Block:</legend>
        <div class="block__title">
            <input class="form__input" type="text" value="<?= $block['title']  ?? ''?>" placeholder="Block title">
        </div>
        <?php self::component('blocks/forms/dashboard', ['selected' => $block['type']]) ?>
        <div class="block__content <?= $block['direction']  ?? ''?> <?= $block['order']  ?? ''?>">
            <div class="editor-block">
                <div class="toolbar-container"></div>
                <div class="content-container">
                    <div class="editor"><?= $block['html']  ?? ''?></div>
                </div>
            </div>
            <div class="editor-block">
                <div class="toolbar-container"></div>
                <div class="content-container">
                    <div class="editor"><?= $block['html']  ?? ''?></div>
                </div>
            </div>
        </div>
    </fieldset>
</div>