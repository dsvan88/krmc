<div class="image__container">
    <!-- <label for="image-input-file" class="image__label"> -->
    <label class="image__label" data-action-click="image/index/form">
        <? if (empty($link)) $link = '/public/images/empty_avatar.webp'; ?>
        <img src="<?= $link ?>" alt="image" class="image__img">
    </label>
    <input type="hidden" name="link">
</div>