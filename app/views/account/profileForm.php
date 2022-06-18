<form class="modal-form" method="POST" action="/account/profile" data-uid="<?= $userData['id'] ?>">
    <h1 class="modal-form__title"><?= $texts['profileFormTitle'] ?></h1>
    <div class="modal-form__divided-block">
        <div class="modal-form__column shrinked">
            <div class="modal-form__row">
                <a class="profile__avatar-place" data-action-click="account/profile/avatar/form" data-uid="<?= $userData['id'] ?>">
                    <?= $userData['avatar'] ?>
                </a>
            </div>
        </div>
        <div class="modal-form__column">
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-fio"><?= $texts['profileFormFioLabel'] ?></label>
                <input type="hidden" name="uid" value="<?= $userData['id'] ?>" />
                <input class="modal-form__input" id="profile-fio" type="text" name="fio" placeholder="П.І.Б." value="<?= $userData['personal']['fio'] ?>" />
            </div>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-birthday"><?= $texts['profileFormBirthdayLabel'] ?></label>
                <input class="modal-form__input datepick" id="profile-birthday" type="text" name="birthday" placeholder="Дата народження" value="<?= date('d.m.Y', $userData['personal']['birthday']) ?>" />
            </div>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-gender"><?= $texts['profileFormGenderLabel'] ?></label>
                <select name="gender" id="profile-gender" class="modal-form__select">
                    <option value="" <?= ($userData['personal']['gender'] === '' ? 'selected' : '') ?>></option>
                    <option value="male" <?= ($userData['personal']['gender'] === 'male' ? 'selected' : '') ?>>Господин</option>
                    <option value="female" <?= ($userData['personal']['gender'] === 'female' ? 'selected' : '') ?>>Госпожа</option>
                    <option value="unknow" <?= ($userData['personal']['gender'] === 'unknow' ? 'selected' : '') ?>>Інші</option>
                </select>
            </div>
            <?= $profileFormStatusesBlock ?>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-email"><?= $texts['profileFormEmailLabel'] ?></label>
                <input class="modal-form__input" id="profile-email" type="email" name="email" placeholder="E-mail" value="<?= $userData['contacts']['email'] ?>" />
            </div>
        </div>
    </div>
    </div>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['formSaveLabel'] ?></button>
        <button type="button" class="modal-close negative"><?= $texts['formCancelLabel'] ?></button>
    </div>
</form>