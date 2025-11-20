<? $idPrefix = mt_rand(0, 10000) ?>
<form>
    <h2 class="modal-form__title"><?= $title ?></h2>
    <div class="input_row big-avatar">
        <img src="<?= $userData['avatar'] ?>" alt="" title="<?= $title ?>" id="image_cropper_<?= $idPrefix ?>">
    </div>

    <? if ($_SESSION['id'] == $userData['id']) : ?>
        <div class="modal-form__button-place">
            <label class="text-accent" for="input_<?= $idPrefix ?>"><?= $texts['ReCropLabel'] ?></label>
            <input type="file" class="hidden" id="input_<?= $idPrefix ?>" data-action-change="new-avatar-input-change">
            <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
        </div>
    <? endif; ?>
</form>