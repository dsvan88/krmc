<div class="profile__card-row">
    <h3 class="profile__card-title"><?= $texts['securityTitle'] ?></h3>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        <?= $texts['passwordLabel'] ?>
    </h5>
    <div class="profile__card-value">
        <?= $texts['passwordText'] ?>
        <i class="text-accent fa fa-pencil" data-action-click="account/password/change/form" title="<?= $texts['editLabel'] ?>"></i>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        <?= $texts['EmailLabel'] ?>
    </h5>
    <div class="profile__card-value">
        <? if (isset($data['email'])) : ?>
            <span class="fa fa-check-circle text-accent"> Approved</span>
        <? else : ?>
            <button type="button" class="positive" data-action-click="verification/email">Підтвердити</button>
        <? endif; ?>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        Телеграм:
    </h5>
    <div class="profile__card-value">
        <? if (isset($data['telegramid'])) : ?>
            <span class="fa fa-check-circle text-accent"> Approved</span>
        <? else : ?>
            <span class="fa fa-times-circle"> Not connected</span>
        <? endif; ?>
    </div>
</div>