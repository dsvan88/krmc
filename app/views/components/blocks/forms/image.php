<div class="block" data-block-type="image">
    <?php self::component('blocks/forms/dashboard', ['selected' => 'image']) ?>
    <div class="image__container">
        <label class="image__label" data-action-click="forms/images/list">
            <?php if (empty($block['imageLink'])) $block['imageLink'] = '/public/images/empty_avatar.webp'; ?>
            <img src="<?= $block['imageLink'] ?>" alt="" class="image__img">
        </label>
        <input type="hidden" name="image_id[]" value="<?= $block['imageId']  ?? ''?>">
        <input type="hidden" name="image_link[]" value="<?= $block['imageLink']  ?? ''?>">
    </div>
</div>
<hr>