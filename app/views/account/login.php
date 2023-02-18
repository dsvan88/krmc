<form class="modal__form" method="POST" action="/account/login">
    <h1 class="modal__form-title"><?= $title ?></h1>
    <div class="modal__row">
        <input class="modal__input" required type="text" name="login" placeholder="<?= $texts['LoginInputPlaceholder'] ?>" autofocus />
    </div>
    <div class="modal__row">
        <input class="modal__input" required type="password" name="password" placeholder="<?= $texts['PasswordInputPlaceholder'] ?>" />
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
    </div>
    <div class="modal__links">
        <a data-action-click="account/forget/form"><?= $texts['ForgetLinkLabel'] ?></a>
        <a data-action-click="account/register/form"><?= $texts['RegisterLinkLabel'] ?></a>
    </div>
</form>