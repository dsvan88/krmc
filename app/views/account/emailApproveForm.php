<form class="modal__form" method="POST" action="/account/email/approve">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <h3 class="modal__form-subtitle">Інcтрукція:</h3>
    <div class="modal__row">
        <span>Ми надіслали лист Вам на пошту з подальшими інструкціями.</span>
    </div>
    <div class="modal__row">
        <label class="modal__row-label" for="approval_code">
            Введіть код підтвердження:
        </label>
        <div class="modal__row-value">
            <input type="text" class="modal__input" name="approval_code" id='approval_code' value="">
        </div>
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>