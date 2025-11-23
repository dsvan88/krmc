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