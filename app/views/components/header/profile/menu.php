<div class='profile__options'>
    <label for='profile-menu-checkbox' class='profile__caret fa fa-caret-down'>
        <!-- <i class='fa fa-caret-down' id='drop-menu'></i> -->
    </label>
    <input type='checkbox' id='profile-menu-checkbox' class='profile__checkbox'>
    <menu class='profile__menu'>
        <?php foreach ($profileMenu as $item) : ?>
            <li class='profile__menu-item'>
                <a href='/<?= $item['link'] ?>'><?= $item['label'] ?></a>
                <div class='profile__menu-bar'></div>
            </li>
        <?php endforeach ?>
    </menu>
</div>