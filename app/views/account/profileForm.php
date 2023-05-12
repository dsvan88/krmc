<form class="modal-form" method="POST" action="/account/profile/<?= $userData['id'] ?>">
    <h1 class="modal-form__title"><?= $title ?></h1>
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
                <label class="modal-form__label" for="profile-fio"><?= $texts['FioLabel'] ?></label>
                <input type="hidden" name="uid" value="<?= $userData['id'] ?>" />
                <input class="modal-form__input" id="profile-fio" type="text" name="fio" placeholder="<?= $texts['FioLabel'] ?>" value="<?= $userData['personal']['fio'] ?>" />
            </div>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-birthday"><?= $texts['BirthdayLabel'] ?></label>
                <input class="modal-form__input datepick" id="profile-birthday" type="text" name="birthday" placeholder="<?= $texts['BirthdayLabel'] ?>" value="<?= date('d.m.Y', $userData['personal']['birthday']) ?>" />
            </div>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-gender"><?= $texts['GenderLabel'] ?></label>
                <select name="gender" id="profile-gender" class="modal-form__select">
                    <option value="" <?= ($userData['personal']['gender'] === '' ? 'selected' : '') ?>></option>
                    <option value="male" <?= ($userData['personal']['gender'] === 'male' ? 'selected' : '') ?>>Господин</option>
                    <option value="female" <?= ($userData['personal']['gender'] === 'female' ? 'selected' : '') ?>>Госпожа</option>
                    <option value="secret" <?= ($userData['personal']['gender'] === 'secret' ? 'selected' : '') ?>>Інші</option>
                </select>
            </div>
            <? if ($_SESSION['privilege']['status'] === 'admin') : ?>
                <div class="modal-form__row">
                    <label class="modal-form__label" for="profile-status">Статус</label>
                    <select class="modal-form__select" id="profile-status" name="status">
                        <option value="" <?= ($userData['privilege']['status'] === '' ? ' selected' : '')  ?>>Гість</option>
                        <option value="user" <?= ($userData['privilege']['status'] === 'user' ? ' selected' : '')  ?>>Користувач</option>
                        <option value="admin" <?= ($userData['privilege']['status'] === 'admin' ? ' selected' : '')  ?>>Админ</option>
                        <option value="manager" <?= ($userData['privilege']['status'] === 'manager' ? ' selected' : '')  ?>>Менеджер</option>
                    </select>
                </div>
            <? endif ?>
            <div class="modal-form__row">
                <label class="modal-form__label" for="profile-email"><?= $texts['EmailLabel'] ?></label>
                <input class="modal-form__input" id="profile-email" type="email" name="email" placeholder="<?= $texts['EmailLabel'] ?>" value="<?= $userData['contacts']['email'] ?>" />
            </div>
        </div>
    </div>
    </div>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['SaveLabel'] ?></button>
        <button type="button" class="modal-close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>