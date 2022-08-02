<a class='header__profile-button' data-action-click='account/profile/form' data-uid='<?= $_SESSION['id'] ?>'>
    <?= $profileImage ?>
</a>
<div class='header__profile-options'>
    <label for='profile-menu-checkbox' class='header__profile-caret'>
        <i class='fa fa-caret-down' id='drop-menu'></i>
    </label>
    <input type='checkbox' id='profile-menu-checkbox' class='header__profile-checkbox' autocomplete='off' />
    <menu class='header__profile-menu'>
        <li class='header__profile-menu-item'>
            <span data-action-click='account/profile/form' data-uid='<?= $_SESSION['id'] ?>'><?= $texts['headerMenuProfileLink'] ?></span>
            <div class='header__profile-menu-bar'></div>
        </li>
        <? if (in_array($_SESSION['privilege']['status'], ['manager', 'admin'])) : ?>
            <li class='header__profile-menu-item'>
                <a href='/news/add'><?= $texts['headerMenuAddNewsLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/news/edit/promo'><?= $texts['headerMenuChangePromoLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/page/add'><?= $texts['headerMenuAddPageLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/users/list'><?= $texts['headerMenuUsersListLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/chat/list'><?= $texts['headerMenuUsersChatsLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/chat/send'><?= $texts['headerMenuChatSendLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
            <li class='header__profile-menu-item'>
                <a href='/settings/list'><?= $texts['headerMenuSettingsListLink'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
        <? endif; ?>
        <li class='header__profile-menu-item'>
            <a href='/account/logout'><?= $texts['headerMenuLogoutLink'] ?></a>
            <div class='header__profile-menu-bar'></div>
        </li>
    </menu>

</div>