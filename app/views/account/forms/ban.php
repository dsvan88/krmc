<form class="modal__form" method="POST" action="/account/ban/<?=$user['id']?>">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <fieldset>
        <legend><?=$texts['bannedTo']?>:</legend>
        <div class="modal__row">
            <input class="modal__input" type="datetime-local" name="ban[expired]" value="<?= $bannedTime ?>" autofocus />
        </div>
    </fieldset>
    <fieldset class="modal__buttons">
        <legend><?=$texts['options']?>:</legend>
        <span class="checkbox-styled">
            <input type="checkbox" name="ban[booking]" id="ban-booking" value="1" class="checkbox-styled-checkbox" <?= empty($user['ban']['booking']) ? '' : 'checked' ?>/>
            <label for="ban-booking" class="checkbox-styled__label"> <?= $texts['booking'] ?> </label>
        </span>
        <span class="checkbox-styled">
            <input type="checkbox" name="ban[auth]" id="ban-auth" value="1" class="checkbox-styled-checkbox" <?= empty($user['ban']['auth']) ? '' : 'checked' ?>/>
            <label for="ban-auth" class="checkbox-styled__label"> <?= $texts['auth'] ?> </label>
        </span>
        <span class="checkbox-styled">
            <input type="checkbox" name="ban[chat]" id="ban-chat" value="1" class="checkbox-styled-checkbox" <?= empty($user['ban']['chat']) ? '' : 'checked' ?>/>
            <label for="ban-chat" class="checkbox-styled__label"> <?= $texts['chat'] ?> </label>
        </span>
    </fieldset>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        <button type="button" class="negative modal__close"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>