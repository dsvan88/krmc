<?php if (empty($block['imageLink'])) $block['imageLink'] = '/public/images/empty_avatar.webp'; ?>

<div class="block" data-block-type="image">
    <fieldset>
        <legend>Block:</legend>
        <div class="block__title">
            <input class="form__input" type="text" value="<?= $block['title']  ?? ''?>" placeholder="Block title">
        </div>
        <?php self::component('blocks/forms/dashboard', ['selected' => 'image']) ?>
        <div class="block__content">
            <div class="image__container">
                <label class="image__label" data-action-click="forms/images/list">
                    <img src="<?= $block['imageLink'] ?>" alt="" class="image__img">
                </label>
                <input type="hidden" name="image_id" value="<?= $block['imageId']  ?? ''?>">
                <input type="hidden" name="image_link" value="<?= $block['imageLink']  ?? ''?>">
            </div>
        </div>
    </fieldset>
</div>