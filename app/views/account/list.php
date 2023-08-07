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
                    <td title="Гендер"><?= empty($usersData[$x]['contacts']['gender']) ? '' : $usersData[$x]['contacts']['gender'] ?></td>
                    <td title="E-mail"><?= empty($usersData[$x]['contacts']['email']) ? '' : $usersData[$x]['contacts']['email'] ?></td>
                    <td title="Telegram"><?= empty($usersData[$x]['contacts']['telegramid']) ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>' ?></td>
                    <td title="Меню">
                        <a class='fa fa-pencil-square-o' href='/account/profile/<?= $usersData[$x]['id'] ?>' title='Редагувати'></a>
                        <span class='fa fa-users' data-action-click='/account/doubles/<?= $usersData[$x]['id'] ?>/form' title='Об’єднати дублікати'></a>
                        <a href="/users/delete/<?= $usersData[$x]['id'] ?>" onclick="return confirm('Are you sure?')" title='Видалити' class="fa fa-trash-o"></a>
                    </td>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
</section>