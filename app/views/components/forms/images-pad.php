<div class="image__container">
    <label class="image__label" data-action-click="forms/images/list">
        <? if (empty($link)) $link = '/public/images/empty_avatar.webp'; ?>
        <img src="<?= $link ?>" alt="" class="image__img">
    </label>
    <input type="hidden" name="image_id">
    <input type="hidden" name="image_link">
</div>