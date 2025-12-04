<tr>
    <td title="Назва"><?= $setting['name'] ?></td>
    <td title="Значення"
        class="settings__value"
        data-action-dblclick="settings/edit"
        data-type="<?= $section ?>"
        data-slug="<?= $setting['slug'] ?>"
        data-name="<?= $setting['name'] ?>"><?= $setting['value'] ?></td>
</tr>