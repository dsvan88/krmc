<form action="/account/profile/edit/<?= $userId ?>/control" method="post">
    <div class="profile__card-row">
        <h3 class="profile__card-title">Керування профілем:</h3>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            Статус:
        </h4>
        <div class="profile__card-value">
            <select name="status">
                <option value="" <?= empty($data['privilege']['status']) ? 'selected' : '' ?>></option>
                <option value="user" <?= $data['privilege']['status'] === 'user' ? 'selected' : '' ?>>Користувач</option>
                <option value="trusted" <?= $data['privilege']['status'] === 'trusted' ? 'selected' : '' ?>>Довірений користувач</option>
                <option value="manager" <?= $data['privilege']['status'] === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                <option value="admin" <?= $data['privilege']['status'] === 'admin' ? 'selected' : '' ?>>Адмін</option>
            </select>
        </div>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            Новий псевдонім:
        </h4>
        <div class="profile__card-value">
            <input type="text" name='name' value="<?= $data['name']?>">
        </div>
    </div>
    <div class="profile__card-row">
        <h4 class="profile__card-label">
            Видалення:
        </h4>
        <div class="profile__card-value">
            <button type="button" class="negative" data-action-click="account/delete/<?=$userId?>">Видалити</button>
        </div>
    </div>
    <div class="profile__card-row buttons">
        <button type='submit' class="positive button"><span class="button__label"><?=$texts['SaveLabel']?></span><i class="fa fa-check button__icon"></i></button>
        <button type='button' class="negative button"><span class="button__label"><?=$texts['CancelLabel']?></span><i class="fa fa-ban button__icon"></i></button>
    </div>
</form>