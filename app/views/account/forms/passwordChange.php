<form class="modal__form" method="POST" action="/account/password/change">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <div class="modal__row">
        <input class="modal__input" type="password" name="password" value="" placeholder="Old password" required>
    </div>
    <div class="modal__row">
        <input class="modal__input" type="password" name="new_password" value="" class="common-form__input" placeholder="New password" required>
    </div>
    <div class="modal__row">
        <input class="modal__input" type="password" name="new_password_confirmation" value="" class="common-form__input" placeholder="Password again" required>
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>