<form class="modal-form" method="POST" action="/account/forget">
    <h1 class="modal-form__title"><?= $texts['formTitle'] ?></h1>
    <h3 class="modal-form__title">Для отримання посилання, розпочніть спілкування з нашим <a href="<?= $texts['tgBotLink'] ?>" target="_blank">ботом</a>,</br> й зареєструйте собі ім’я, якщо ви цього не зробили раніше.</h1>
        <div class="modal-form__row">
            <input class="modal-form__input" required type="text" name="auth" placeholder="<?= $texts['authPlaceholder'] ?>" autofocus />
        </div>
        <div class="modal-form__button-place">
            <button type="submit" class="positive"><?= $texts['formSubmitLabel'] ?></button>
            <button type="button" class="modal-close negative"><?= $texts['formCancelLabel'] ?></button>
        </div>
</form>