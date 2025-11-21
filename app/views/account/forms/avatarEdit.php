<? $idPrefix = mt_rand(0, 10000) ?>
<form enctype='multipart/form-data'>
    <!-- <h2 class="modal__subtitle"><?= $title ?></h2> -->
    <div class="input_row big-avatar">
        <input type="hidden" name="uid" value="<?=$userData['id']?>">
        <div class="big-avatar__dashboard">
            <label class="big-avatar__change fa fa-refresh" for="input_<?= $idPrefix ?>" title="<?= $texts['ReCropLabel'] ?>"></label>
            <input type="file" class="hidden" id="input_<?= $idPrefix ?>" data-action-change="new-avatar-input-change">
        </div>
        <img src="<?= $userData['avatar'] ?>" alt="" title="<?= $title ?>" id="image_cropper_<?= $idPrefix ?>">
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SaveLabel'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>