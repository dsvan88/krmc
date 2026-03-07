<form class="modal__form" method="POST" data-action-submit="/account/login">
    <fieldset>
        <legend class="modal__subtitle"><?= $subtitle ?></legend>
        <div class="modal__row">
            <input class="modal__input" required type="text" name="login" placeholder="<?= $texts['LoginInputPlaceholder'] ?>" autofocus />
        </div>
        <div class="modal__row">
            <input class="modal__input" required type="password" name="password" placeholder="<?= $texts['PasswordInputPlaceholder'] ?>" />
            <?php self::component('csrf') ?>
        </div>
        <div class="modal__buttons">
            <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </fieldset>
    <div class="modal__links">
        <a data-action-click="account/forget/form"><?= $texts['ForgetLinkLabel'] ?></a>
        <a data-action-click="account/register/form"><?= $texts['RegisterLinkLabel'] ?></a>
    </div>
</form>