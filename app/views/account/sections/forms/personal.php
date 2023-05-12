<form action="/account/profile/edit/<?= $userId ?>/personal" method="post">
    <div class="profile__card-row">
        <h3 class="profile__card-title">Справа № <span class="text-accent"><?= $userId ?></span>:</h3>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['FioLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <input type="text" name='fio' value="<?= $data['personal']['fio'] ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <input type="date" name='birthday' value="<?= date('Y-m-d', $data['personal']['birthday']) ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['GenderLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <select name="gender">
                <option value=""></option>
                <option value="male">Пан</option>
                <option value="female">Пані</option>
                <option value="secret">Дехто</option>
            </select>
        </div>
    </div>
    <div class="profile__card-row buttons">
        <button type='submit' class="positive button"><span class="button__label"><?=$texts['SaveLabel']?></span><i class="fa fa-check button__icon"></i></button>
        <button class="negative button"><span class="button__label"><?=$texts['CancelLabel']?></span><i class="fa fa-ban button__icon"></i></button>
    </div>
</form>