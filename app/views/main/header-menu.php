<a class='header__profile-button' href='/account/profile/<?= $_SESSION['id'] ?>' title="<?= $texts['headerMenuProfileLink'] ?>" alt="<?= $texts['headerMenuProfileLink'] ?>">
    <?= $profileImage ?>
</a>
<div class='header__profile-options'>
    <label for='profile-menu-checkbox' class='header__profile-caret'>
        <i class='fa fa-caret-down' id='drop-menu'></i>
    </label>
    <input type='checkbox' id='profile-menu-checkbox' class='header__profile-checkbox'>
    <menu class='header__profile-menu'>
        <li class='header__profile-menu-item'>
            <a href='/account/profile/<?= $_SESSION['id'] ?>'><?= $texts['headerMenuProfileLink'] ?></span>
                <div class='header__profile-menu-bar'></div>
        </li>
        <li class='header__profile-menu-item'>
            <a href='/account/logout'><?= $texts['headerMenuLogoutLink'] ?></a>
            <div class='header__profile-menu-bar'></div>
        </li>
    </menu>
</div>