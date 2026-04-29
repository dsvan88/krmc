<form class="modal__form" method="POST" data-action-submit="/coupons/add">
    <fieldset>
        <legend class="modal__subtitle"><?= $subtitle ?></legend>
        <div class="modal__row">
            <select class="modal__select" name="type">
                <option value="0">discount: 50%</option>
                <option value="1">discount: 100%</option>
                <option value="2">discount: 100</option>
            </select>
        </div>
        <div class="modal__row">
            <input class="modal__input" required type="text" name="name" placeholder="Псевдонім" autofocus data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" />
            <?php self::component('csrf') ?>
            <datalist id="users-names-list"> </datalist>
        </div>
        <div class="modal__buttons">
            <button type="submit" class="positive"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </fieldset>
</form>