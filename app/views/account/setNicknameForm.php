<form class="modal-form" method="POST" action="/account/set/nickname/<?= $chatData['uid'] ?>">
    <h1 class="modal-form__title"><?= $title ?></h1>
    <div class="modal-form__row">
        <input class="modal-form__input" required type="text" name="name" placeholder="Псевдонім" autofocus data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
    </div>
    <datalist id="users-names-list"> </datalist>
    <div class="modal-form__button-place">
        <button type="submit" class="positive"><?= $texts['SaveLabel'] ?></button>
        <button type="button" class="modal-close negative"><?= $texts['CancelLabel'] ?></button>
    </div>
</form>