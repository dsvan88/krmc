<form>
    <h2 class="modal-form__title"><?= $title ?></h2>
    <div class="input_row big-avatar">
        <?= $userData['avatar'] ?>
    </div>

    <? if ($_SESSION['id'] == $userData['id']) : ?>
        <div class="modal-form__button-place">
            <button type="button" class="positive" data-action-click="account/profile/avatar/recrop/form"><?= $texts['ReCropLabel'] ?></button>
            <button type="button" class="modal-close negative"><?= $texts['CancelLabel'] ?></button>
        </div>
    <? endif; ?>
</form>