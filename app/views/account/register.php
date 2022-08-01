<form class="modal-form" method="POST" action="/account/register">
    <h1 class="modal-form__title"><?= $title ?></h1>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="login" placeholder="<?= $texts['LoginLabel'] ?>" autofocus />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="name" placeholder="<?= $texts['NameLabel'] ?>" data-autocomplete="user-name" />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="password" placeholder="<?= $texts['PasswordLabel'] ?>" />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="chk_password" placeholder="<?= $texts['PasswordAgainLabel'] ?>" />
    </div>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['RegisterSubmit'] ?></button>
        <button type="button" class="modal-close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>