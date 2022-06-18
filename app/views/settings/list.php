<section class="section section-users-list">
    <table class="users-list" style="width:100%">
        <thead>
            <tr>
                <th>Тип</th>
                <th>Назва</th>
                <th>Значення</th>
            </tr>
        </thead>
        <tbody>
            <? for ($x = 0; $x < count($settingsData); $x++) : ?>
                <tr>
                    <td title="Тип"><?= $settingsData[$x]['type'] ?></td>
                    <td title="Назва"><?= $settingsData[$x]['name'] ?></td>
                    <td title="Значення"><?= $settingsData[$x]['value'] ?></td>
                    <td title="Меню">
                        <a href="/settings/edit/<?= $settingsData[$x]['id'] ?>" title='Редагувати'><i class='fa fa-pencil-square-o news-dashboard__button'></i></a>
                        <a href="/settings/delete/<?= $settingsData[$x]['id'] ?>" onclick="return confirm('Are you sure?')" title='Видалити'><i class='fa fa-trash-o news-dashboard__button'></i></a>
                    </td>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
    <a href="/settings/add">Додати</a>
</section>