<form action="account/profile/section/credo/edit" method="post">
    <div class="profile__card-row">
        <h3 class="profile__card-title">Підслухано:</h3>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['CredoLiveLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['personal']['fio'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['CredoGameLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= date('d.m.Y', $data['personal']['birthday']) ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['BestQuoteLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['personal']['gender'] ?>
        </div>
    </div>
    <div class="profile__card-row">
        <h5 class="profile__card-label">
            <?= $texts['SignatureLabel'] ?>:
        </h5>
        <div class="profile__card-value">
            <?= $data['personal']['gender'] ?>
        </div>
    </div>
    <div class="profile__card-row buttons">
        <button type='submit' class="positive button"><span class="button__label"><?=$texts['SaveLabel']?></span><i class="fa fa-check button__icon"></i></button>
        <button class="negative button"><span class="button__label"><?=$texts['CancelLabel']?></span><i class="fa fa-ban button__icon"></i></button>
    </div>
</form>