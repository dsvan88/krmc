<form class="modal__form" method="POST" action="/account/forget">
    <h1 class="modal__form-title"><?= $title ?></h1>
    <h3 class="modal__form-subtitle">Для отримання посилання, розпочніть спілкування з нашим <a href="<?= $texts['tgBotLink'] ?>" target="_blank">ботом</a>,</br> й зареєструйте собі ім’я, якщо ви цього не зробили раніше.</h3>
    <div class="modal__row">
        <input class="modal__input" required type="text" name="auth" placeholder="<?= $texts['authPlaceholder'] ?>" autofocus>
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>