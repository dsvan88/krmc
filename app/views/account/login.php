<form class="modal-form" method="POST" action="/account/login">
    <h1 class="modal-form__title"><?= $title ?></h1>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="login" placeholder="<?= $texts['LoginInputPlaceholder'] ?>" autofocus />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="password" placeholder="<?= $texts['PasswordInputPlaceholder'] ?>" />
    </div>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
    </div>
    <div class="modal-form__links-place">
        <a data-action-click="account/forget/form"><?= $texts['ForgetLinkLabel'] ?></a>
        <a data-action-click="account/register/form"><?= $texts['RegisterLinkLabel'] ?></a>
    </div>
</form>