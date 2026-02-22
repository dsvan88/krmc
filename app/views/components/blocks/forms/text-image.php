<?php
    $blockType = empty($block['order']) ? $blockType : 'image-text';
?>
<div class="block ti__wrapper" data-block-type="<?= $blockType ?>">
    <?php self::component('blocks/forms/dashboard', ['selected' => $blockType]) ?>
    <div class="block__content <?= $block['direction']  ?? ''?> <?= $block['order']  ?? ''?>">
        <div class="editor-block" data-field="html">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"><?= $block['html']  ?? ''?></div>
            </div>
        </div>
        <div class="image__container">
            <label class="image__label" data-action-click="forms/images/list">
                <?php if (empty($block['imageLink'])) $block['imageLink'] = '/public/images/empty_avatar.webp'; ?>
                <img src="<?= $block['imageLink'] ?>" alt="" class="image__img">
            </label>
            <input type="hidden" name="image_id[]" value="<?= $block['imageId']  ?? ''?>">
            <input type="hidden" name="image_link[]" value="<?= $block['imageLink']  ?? ''?>">
        </div>
    </div>
</div>