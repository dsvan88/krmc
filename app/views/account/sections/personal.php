<div class="profile__card-row">
    <h3 class="profile__card-title">Справа № <span class="text-accent"><?= $userId ?></span>:</h3>
</div>
<fieldset>
    <legend>Особисті дані</legend>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            Псевдонім:
        </h5>
        <div class="profile__card-value">
            <?= $data['name'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['FioLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= empty($data['personal']['fio']) ? '' : $data['personal']['fio'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= empty($data['personal']['birthday']) ? '' : date('d.m.Y', $data['personal']['birthday']) ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['GenderLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['personal']['genderName'] ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Явочні контакти</legend>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['EmailLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['email'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['TelegramLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['telegram'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['PhoneLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['phone'] ?>
        </div>
    </div>
</fieldset>