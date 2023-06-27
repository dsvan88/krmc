<form class="modal__form" method="POST" action="/add-participant">
    <h1 class="modal__form-title"><?= $title ?></h1>
    <div class="modal__row">
        <input class="modal__input" required type="text" name="name" placeholder="Псевдонім" autofocus data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
    </div>
    <datalist id="users-names-list"> </datalist>
    <div class="modal__buttons">
        <button type="submit" class="positive"><?= $texts['SaveLabel'] ?></button>
        <button type="button" class="modal__close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>