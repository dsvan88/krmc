<form class="modal-form" method="POST" action="account/register">
    <!-- <picture>
			<source srcset="../images/logo.webp" type="image/webp" />
			<img class="modal-form__logo" title="My logo" alt="My logo" src="./images/logo.png" />
		</picture> -->
    <h1 class="modal-form__title"><?= $texts['formTitle'] ?></h1>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="login" placeholder="<?= $texts['accountLoginLabel'] ?>" autofocus />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="name" placeholder="<?= $texts['accountNameLabel'] ?>" data-autocomplete="user-name" />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="password" placeholder="<?= $texts['accountPasswordLabel'] ?>" />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="password" name="chk_password" placeholder="<?= $texts['accountPasswordAgainLabel'] ?>" />
    </div>
    <!--     <h3 class="modal-form__row-title">Для Telegram-бота</h3>
    <div class="modal-form__row">
        <input class="modal-form__input" type="text" name="telegram" placeholder="Telegram username" />
    </div>
    <div class="modal-form__row">
        <input class="modal-form__input" type="email" name="email" placeholder="E-mail" />
    </div> -->
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['accountRegisterSubmit'] ?></button>
        <button type="button" class="modal-close negative"><?= $texts['formCancelLabel'] ?></button>
    </div>
</form>