<div class="image__container">
    <label for="image-input-file" class="image__label">
        <? if (empty($link)) $link = '/public/images/empty_avatar.webp'; ?>
        <img src="<?= $link ?>" alt="" class="image__img">
    </label>
    <input type="file" placeholder="Image" class="form__input image" id="image-input-file" data-action-change="form-image-change" accept='image/*'>
    <input type="hidden" name="image">
    <input type="hidden" name="filename">
</div>