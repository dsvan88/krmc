<form>
    <h2 class="modal-form__title"><?= $texts['profileAvatarFormTitle'] ?></h2>
    <div class="input_row big-avatar">
        <?= $userData['avatar'] ?>
    </div>

    <? if ($_SESSION['id'] == $userData['id']) : ?>
        <div class="modal-form__button-place">
            <button type="button" class="positive" data-action-click="account/profile/avatar/recrop/form"><?= $texts['profileAvatarFormReCropTitle'] ?></button>
            <button type="button" class="modal-close negative"><?= $texts['formCancelLabel'] ?></button>
        </div>
    <? endif; ?>
</form>