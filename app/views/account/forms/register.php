<form class="modal__form" method="POST" action="/account/register">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <div class="modal__row">
        <input class="modal__input" required type="text" name="login" placeholder="<?= $texts['LoginLabel'] ?>" autofocus />
        <? self::component('csrf') ?>
    </div>
    <div class="modal__row">
        <input class="modal__input" required type="text" name="name" placeholder="<?= $texts['NameLabel'] ?>" data-autocomplete="user-name" />
    </div>
    <div class="modal__row">
        <input class="modal__input" required type="password" name="password" placeholder="<?= $texts['PasswordLabel'] ?>" />
    </div>
    <div class="modal__row">
        <input class="modal__input" required type="password" name="chk_password" placeholder="<?= $texts['PasswordAgainLabel'] ?>" />
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['RegisterSubmit'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>