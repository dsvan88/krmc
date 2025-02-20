<form class="modal__form" method="POST" action="/settings/edit/<?= $setting['id'] ?>">
    <h2 class="modal__form-title"><?= $title ?></h2>
    <h3 class="modal__form-subtitle" title="Назва"><?= $setting['name'] ?></h3>
    <div class="modal__row">
        <input class="modal__input" type="text" name="value" value="<?= $setting['value'] ?>" placeholder="Значення" />
    </div>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
    </div>
</form>