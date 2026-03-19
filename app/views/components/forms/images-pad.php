<?php if (empty($link)) $link = '/public/images/empty_avatar.webp' ?>
<div class="image__container">
    <label class="image__label" data-action-click="forms/images/list">
        <img src="<?= $link ?>" alt="" class="image__img">
    </label>
    <input type="hidden" name="image_id" value="<?= $imageId ?? '' ?>">
    <input type="hidden" name="image_link" value="<?= $link ?>">
</div>