<div class="profile__card-row">
    <h3 class="profile__card-title">Справа № <span class="text-accent"><?= $userId ?></span>:</h3>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        <?= $texts['FioLabel'] ?>:
    </h5>
    <div class="profile__card-value">
        <?= $data['personal']['fio'] ?>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        <?= $texts['BirthdayLabel'] ?>:
    </h5>
    <div class="profile__card-value">
        <?= date('d.m.Y', $data['personal']['birthday']) ?>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        <?= $texts['GenderLabel'] ?>:
    </h5>
    <div class="profile__card-value">
        <?= $data['personal']['gender'] ?>
    </div>
</div>