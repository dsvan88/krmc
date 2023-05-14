<div class="modal__form">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <h3 class="modal__form-subtitle">Інcтрукція:</h3>
    <div class="modal__row">
        <span>Для підтвердження телеграму - розпочність спілкування:</span>
    </div>
    <div class="modal__row">
        <ul class="modal__list">
            <li class="modal__list-item">у <a href="<?= $contacts['telegram']['value'] ?>" target="_blank">нашій группі</a>, або</li>
            <li class="modal__list-item">з <a href="https://t.me/<?= $contacts['tg-chatbot']['value'] ?>" target="_blank">нашим ботом</a></li>
        </ul>
    </div>
    <div class="modal__row">
        <span>й зареєструйте собі ігрове ім’я командою:</span>
    </div>
    <div class="modal__row-accent">
        <h4 class="text-accent">/nick <?= $userName ?></h4>
    </div>
    <div class="modal__buttons">
        <button type="button" class="modal__close positive"><?= $texts['AgreeLabel'] ?></button>
    </div>
</div>