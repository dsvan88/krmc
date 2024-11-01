<? if (empty($menuItem['menu'])) : ?>
    <div class='navigation__item'>
        <a href='/<?= $menuItem['path'] ?>'><?= $menuItem['label'] ?></a>
        <div class='bar'></div>
    </div>
<? else: ?>
    <div class="navigation__item dropdown">
        <label class="dropdown__label"><?= empty($menuItem['path']) ? $menuItem['label'] : "<a href='/{$menuItem['path']}'>{$menuItem['label']}</a>" ?>
        </label>
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