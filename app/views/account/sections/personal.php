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
    <?php if (!empty($data['status'])): ?>
        <div class="profile__card-row">
            <h5 class="profile__card-label">
                <?= $texts['emojiLabel'] ?>
            </h5>
            <div class="profile__card-value" <?php if ($isAdmin): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.emoji" <?php endif ?>>
                <?= empty($data['personal']['emoji']) ? '&nbsp;' : $data['personal']['emoji'] ?>
            </div>
        </div>
    <?php endif ?>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['FioLabel'] ?>
        </h5>
        <div class="profile__card-value" <?php if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.fio" <?php endif ?>>
            <?= empty($data['personal']['fio']) ? '&nbsp;' : $data['personal']['fio'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['BirthdayLabel'] ?>
        </h5>
        <div class="profile__card-value" <?php if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.birthday" data-type="date" <?php endif ?>>
            <?= empty($data['personal']['birthday']) ? '&nbsp;' : date('d.m.Y', $data['personal']['birthday']) ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['GenderLabel'] ?>
        </h5>
        <div class="profile__card-value" <?php if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="personal.gender" <?php endif ?>>
            <?= empty($data['personal']['genderName']) ? '&nbsp' : $data['personal']['genderName'] ?>
        </div>
    </div>
</fieldset>

<?php if ($isAdmin || $isSelf): ?>
    <fieldset>
        <legend><?= $texts['contactsLabel'] ?></legend>
        <div class="profile__card-row">
            <h5 class="profile__card-label">
                <?= $texts['EmailLabel'] ?>
            </h5>
            <?php if ($isAdmin): ?>
                <div class="profile__card-value" <?php if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="contacts.email" <?php endif ?>>
                    <?php if (isset($data['approved']['email'])): ?>
                        <?= $data['email'] ?><i class="fa fa-check-circle text-accent"></i>
                    <?php else: ?>
                        <?= $data['email__value'] ?><i class="fa fa-times text-accent" title="Не підтвердженно"></i>
                    <?php endif ?>
                    <?php if ($isSelf && !empty($data['email__value']) && empty($data['approved']['email'])): ?>
                        <span class="text-accent small" data-action-click="verification/email"><?= $texts['approveLabel'] ?></span>
                    <?endif?>
                </div>
                <?php else: ?>
                     <div class="profile__card-value">
                    <?php if (isset($data['approved']['email'])): ?>
                        <?= $data['email__value'] ?><i class="fa fa-check-circle text-accent"></i>
                    <?php else: ?>
                        <?php if (!empty($data['email__value']) && empty($data['approved']['email'])) :?> 
                            <?= $data['email__value'] ?><span class="text-accent small" data-action-click="verification/email"><?= $texts['approveLabel'] ?></span>
                        <?php endif ?>
                    <?php endif ?>
                </div>
                <?endif?>
        </div>
        <div class="profile__card-row">
            <h5 class="profile__card-label">
                <?= $texts['TelegramLabel'] ?>
            </h5>
            <div class="profile__card-value">
                <?= empty($data['telegram']) ? '< No Data >' : $data['telegram'] ?>
                <?php if (isset($data['approved']['telegramid'])): ?>
                    <i class="fa fa-check-circle text-accent"></i>
                <?php endif ?>
            </div>
        </div>
        <div class="profile__card-row">
            <h5 class="profile__card-label">
                <?= $texts['PhoneLabel'] ?>
            </h5>
            <div class="profile__card-value" <?php if ($isAdmin || $isSelf): ?> data-action-dblclick="account/personal/edit" data-user-id="<?= $data['id'] ?>" data-field="contacts.phone" data-type="tel" <?php endif ?>>
                <?= $data['phone'] ?>
            </div>
        </div>
    </fieldset>
<?php endif ?>