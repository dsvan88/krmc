<div class='header__profile-options'>
    <label for='profile-menu-checkbox' class='header__profile-caret'>
        <i class='fa fa-caret-down' id='drop-menu'></i>
    </label>
    <input type='checkbox' id='profile-menu-checkbox' class='header__profile-checkbox'>
    <menu class='header__profile-menu'>
        <? foreach ($profileMenu as $item) : ?>
            <li class='header__profile-menu-item'>
                <a href='/<?= $item['link'] ?>'><?= $item['label'] ?></a>
                <div class='header__profile-menu-bar'></div>
            </li>
        <? endforeach ?>
    </menu>
</div>