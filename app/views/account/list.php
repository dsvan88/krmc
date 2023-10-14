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
                <th>Бан</th>
                <th class="users-list__dashboard">Меню</th>
            </tr>
        </thead>
        <tbody>
            <? for ($x = 0; $x < count($usersData); $x++) : ?>
                <tr>
                    <td><?= ($x + 1) ?>.</td>
                    <td title="Псевдонім" class="users-list__name"><a href="/account/profile/<?=$usersData[$x]['id']?>" target="_blank"><?= $usersData[$x]['name'] ?></a></td>
                    <td title="Реєстрація"><?= empty($usersData[$x]['login']) ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>' ?></td>
                    <td title="Статус"><?= $usersData[$x]['privilege']['status'] ?></td>
                    <td title="Гендер"><?= empty($usersData[$x]['contacts']['gender']) ? '' : $usersData[$x]['contacts']['gender'] ?></td>
                    <td title="E-mail"><?= empty($usersData[$x]['contacts']['email']) ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>' ?></td>
                    <td title="Telegram"><?= empty($usersData[$x]['contacts']['telegramid']) ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>' ?></td>
                    <td title="Бан"><?= empty($usersData[$x]['ban']) || $usersData[$x]['ban']['expired'] < $_SERVER['REQUEST_TIME'] ? '<i class="fa fa-square-o"></i>' : '<i class="fa fa-check-square-o"></i>' ?></td>
                    <td title="Меню" class="users-list__dashboard">
                        <span class='fa fa-ban' data-action-click='/account/ban/form' data-user-id="<?= $usersData[$x]['id'] ?>" title='Забанити користувача'></span>
                        <span class='fa fa-users' data-action-click='/account/doubles/<?= $usersData[$x]['id'] ?>/form' title='Об’єднати дублікати'></span>
                        <span class='fa fa-user-times' data-action-click='account/delete' data-user-id="<?= $usersData[$x]['id'] ?>" data-verification="verification/root" title='Видалити'></span>
                    </td>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
</section>