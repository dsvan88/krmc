
<tr>
    <td title="Назва"><?= $setting['name'] ?></td>
    <td title="Значення" class="settings__value" data-action-dblclick="settings/edit/form" data-type="<?= $section ?>" data-slug="<?= $setting['slug'] ?>"><?= $setting['value'] ?></td>
    <td title="Меню" class="settings__dasboard">
        <span class="fa fa-pencil-square-o" data-action-click="settings/edit/form" data-type="<?= $section ?>" data-slug="<?= $setting['slug'] ?>" title='<?=$texts['edit']?>'></a>
    </td>
</tr>