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
    <? if (!empty($data['status'])): ?>
        <div class="profile__card-row">
            <h5 class="profile__card-label">
                <?= $texts['emojiLabel'] ?>
            </h5>
            <div class="profile__card-value" <? if ($isAdmin): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.emoji" <? endif ?>>
                <?= empty($data['personal']['emoji']) ? '&nbsp;' : $data['personal']['emoji'] ?>
            </div>
        </div>
    <? endif ?>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['FioLabel'] ?>
        </h5>
        <div class="profile__card-value" <? if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.fio" <? endif ?>>
            <?= empty($data['personal']['fio']) ? '&nbsp;' : $data['personal']['fio'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>
        </h5>
        <div class="profile__card-value" <? if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.birthday" data-type="date" <? endif ?>>
            <?= empty($data['personal']['birthday']) ? '&nbsp;' : date('d.m.Y', $data['personal']['birthday']) ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['GenderLabel'] ?>
        </h5>
        <div class="profile__card-value" <? if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.gender" <? endif ?>>
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
                <? if ($isSelf): ?>
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