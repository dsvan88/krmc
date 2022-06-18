<form class="modal-form" method="POST" action="/account/login">
    <h1 class="modal-form__title"><?= $loginFormTitle ?></h1>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="login" placeholder="<?= $loginFormLoginInputPlaceholder ?>" autofocus />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="password" placeholder="<?= $loginFormPasswordInputPlaceholder ?>" />
    </div>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $loginFormSubmitTitle ?></button>
    </div>
    <div class="modal-form__links-place">
        <a data-action-click="account/forget/form"><?= $loginFormForgetLink ?></a>
        <a data-action-click="account/register/form"><?= $loginFormRegisterLink ?></a>
    </div>
</form>