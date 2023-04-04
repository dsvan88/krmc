<section class="section section-users-list">
    <table class="users-list" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Псевдонім</th>
                <th>Логін</th>
                <th>Статус</th>
                <th>Гендер</th>
                <th>E-mail</th>
                <th>Telegram</th>
                <th>Меню</th>
            </tr>
        </thead>
        <tbody>
            <? for ($x = 0; $x < count($usersData); $x++) : ?>
                <tr>
                    <td><?= ($x + 1) ?>.</td>
                    <td title="Псевдонім"><?= $usersData[$x]['name'] ?></td>
                    <td title="Логін"><?= $usersData[$x]['login'] ?></td>
                    <td title="Статус"><?= $usersData[$x]['privilege']['status'] ?></td>
                    <td title="Гендер"><?= isset($usersData[$x]['contacts']['gender']) ? $usersData[$x]['contacts']['gender'] : '' ?></td>
                    <td title="E-mail"><?= isset($usersData[$x]['contacts']['email']) ? $usersData[$x]['contacts']['email'] : '' ?></td>
                    <td title="Telegram"><?= $usersData[$x]['contacts']['telegramid'] !== '' ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>' ?></td>
                    <td title="Меню">
                        <i class='fa fa-pencil-square-o news-dashboard__button' data-action-click='account/profile/form' data-uid=' <?= $usersData[$x]['id'] ?>' title='Редагувати'></i>
                        <a href="/users/delete/<?= $usersData[$x]['id'] ?>" onclick="return confirm('Are you sure?')" title='Видалити'><i class='fa fa-trash-o news-dashboard__button'></i></a>
                    </td>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
</section>