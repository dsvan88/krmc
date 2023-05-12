<div class="profile__card-row">
    <h3 class="profile__card-title">Підслухано:</h3>
</div>
<div class="profile__card-row">
    <div class="profile__card-label">
        <?= $texts['CredoLiveLabel'] ?>:
    </div>
    <div class="profile__card-value">
        <?= $data['personal']['fio'] ?>
    </div>
</div>
<div class="profile__card-row">
    <div class="profile__card-label">
        <?= $texts['CredoGameLabel'] ?>:
    </div>
    <div class="profile__card-value">
        <?= date('d.m.Y', $data['personal']['birthday']) ?>
    </div>
</div>
<div class="profile__card-row">
    <div class="profile__card-label">
        <?= $texts['BestQuoteLabel'] ?>:
    </div>
    <div class="profile__card-value">
        <?= $data['personal']['gender'] ?>
    </div>
</div>
<div class="profile__card-row">
    <div class="profile__card-label">
        <?= $texts['SignatureLabel'] ?>:
    </div>
    <div class="profile__card-value">
        <?= $data['personal']['gender'] ?>
    </div>
</div>