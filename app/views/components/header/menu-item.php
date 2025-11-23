<? if (empty($menuItem['menu'])) : ?>
    <div class='navigation__item'>
        <div class="navigation__label">
            <a href='/<?= $menuItem['path'] ?>'><?= $menuItem['label'] ?></a>
        </div>
        <div class='bar'></div>
    </div>
<? else: ?>
    <? $itemId = $menuItem['type'] . '_' . mt_rand(0, 1000) ?>
    <div class="navigation__item dropdown">
        <input type="checkbox" name="dropdown-checkbox" class="dropdown__checkbox" id="<?= $itemId ?>">
        <span class="dropdown__label"><?= empty($menuItem['path']) ? $menuItem['label'] : "<a href='/{$menuItem['path']}'>{$menuItem['label']}</a>" ?><label class="dropdown__toggle fa fa-chevron-down" for="<?= $itemId ?>"></label></span>
        <div class="bar"></div>
        <menu class="dropdown__menu">
            <? foreach ($menuItem['menu'] as $key => $menuSubItem): ?>
                <li class='dropdown__item'>
                    <a href='/<?= $menuSubItem['slug'] === 'index' ? '' : $menuItem['type'] . '/' . $menuSubItem['slug'] ?>/'><?= $menuSubItem['name'] ?></a>
                    <div class='dropdown__bar'></div>
                </li>
            <? endforeach ?>
        </menu>
    </div>
<? endif ?>