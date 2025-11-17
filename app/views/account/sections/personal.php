<fieldset>
    <legend><?= $texts['personalTitle'] ?></legend>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['nickLabel'] ?>
        </h5>
        <div class="profile__card-value">
            <?= $data['name'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['FioLabel'] ?>
        </h5>
        <div class="profile__card-value" data-action-dblclick="account/personal/edit" data-field="fio">
            <?= empty($data['personal']['fio']) ? '' : $data['personal']['fio'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>
        </h5>
        <div class="profile__card-value" data-action-dblclick="account/personal/edit" data-field="birthday" data-type="date">
            <?= empty($data['personal']['birthday']) ? '' : date('d.m.Y', $data['personal']['birthday']) ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['GenderLabel'] ?>
        </h5>
        <div class="profile__card-value">
            <?= $data['personal']['genderName'] ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?= $texts['contactsLabel'] ?></legend>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['EmailLabel'] ?>
        </h5>
        <div class="profile__card-value">
            <?= $data['email'] ?>
            <? if (isset($data['approved']['email'])): ?>
                <i class="fa fa-check-circle text-accent"></i>
            <? else: ?>
                <? if ($_SESSION['id'] == $data['id']): ?>
                    <span class="text-accent small" data-action-click="verification/email">Підтвердити</span>
                <? else: ?>
                    <i class="fa fa-times text-accent" title="Не підтвердженно"></i>
                <? endif ?>
            <? endif ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['TelegramLabel'] ?>
        </h5>
        <div class="profile__card-value">
            <?= $data['telegram'] ?>
            <? if (isset($data['approved']['telegramid'])): ?>
                <i class="fa fa-check-circle text-accent"></i>
            <? endif ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['PhoneLabel'] ?>
        </h5>
        <div class="profile__card-value">
            <?= $data['phone'] ?>
        </div>
    </div>
</fieldset>