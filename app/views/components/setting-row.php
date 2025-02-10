
<tr>
    <td title="Назва"><?= $setting['name'] ?></td>
    <td title="Значення" class="settings__value" data-action-dblclick="settings/edit/form" data-setting-id="<?= $setting['id'] ?>"><?= $setting['value'] ?></td>
    <td title="Меню" class="settings__dasboard">
        <span class="fa fa-pencil-square-o" data-action-click="settings/edit/form" data-setting-id="<?= $setting['id'] ?>" title='<?=$texts['edit']?>'></a>
        <?/*<!-- <a href="/settings/delete/<?= $setting['id'] ?>" onclick="return confirm('Are you sure?')" title='Видалити'><i class='fa fa-trash-o news-dashboard__button'></i></a>*/?>
    </td>
</tr>