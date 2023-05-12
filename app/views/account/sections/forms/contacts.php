<form action="/account/profile/edit/<?= $userId ?>/contacts" method="post">
    <div class="profile__card-row">
        <h3 class="profile__card-title">Явочні контакти:</h3>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['EmailLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <input type="text" name='email' value="<?= $data['email'] ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['TelegramLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <input type="text" name='telegram' value="<?= $data['telegram'] ?>">
        </div>
    </div>
    <div class="profile__card-row">
        <div class="profile__card-label">
            <?= $texts['PhoneLabel'] ?>:
        </div>
        <div class="profile__card-value">
            <input type="text" name='phone' value="<?= $data['phone'] ?>">
        </div>
    </div>
    <div class="profile__card-row buttons">
        <button type='submit' class="positive button"><span class="button__label"><?=$texts['SaveLabel']?></span><i class="fa fa-check button__icon"></i></button>
        <button type='button' class="negative button"><span class="button__label"><?=$texts['CancelLabel']?></span><i class="fa fa-ban button__icon"></i></button>
    </div>
</form>