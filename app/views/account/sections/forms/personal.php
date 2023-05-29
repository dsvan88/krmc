<form action="/account/profile/edit/<?= $userId ?>/personal" method="post">
    <div class="profile__card-row">
        <h3 class="profile__card-title">Справа № <span class="text-accent"><?= $userId ?></span>:</h3>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            <?= $texts['FioLabel'] ?>:
        </h4>
        <div class="profile__card-value">
            <input type="text" name='fio' value="<?= $data['personal']['fio'] ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>:
        </h4>
        <div class="profile__card-value">
            <input type="date" name='birthday' value="<?= date('Y-m-d', $data['personal']['birthday']) ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            <?= $texts['GenderLabel'] ?>:
        </h4>
        <div class="profile__card-value">
            <select name="gender">
                <option value="" <?= empty($data['personal']['gender']) ? 'selected' : '' ?>></option>
                <option value="male" <?= $data['personal']['gender'] === 'male' ? 'selected' : '' ?>>Пан</option>
                <option value="female" <?= $data['personal']['gender'] === 'female' ? 'selected' : '' ?>>Пані</option>
                <option value="secret" <?= $data['personal']['gender'] === 'secret' ? 'selected' : '' ?>>Дехто</option>
            </select>
        </div>
    </div>
    <div class="profile__card-row buttons">
        <button type='submit' class="positive button"><span class="button__label"><?=$texts['SaveLabel']?></span><i class="fa fa-check button__icon"></i></button>
        <button type='button' class="negative button"><span class="button__label"><?=$texts['CancelLabel']?></span><i class="fa fa-ban button__icon"></i></button>
    </div>
</form>