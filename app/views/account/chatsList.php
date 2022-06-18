<section class="section section-users-list">
    <? if (!empty($chatsData)) : ?>
        <table class="users-list" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Чат з</th>
                    <th>Псевдонім</th>
                    <th>Остання активність</th>
                </tr>
            </thead>
            <tbody>
                <? for ($x = 0; $x < count($chatsData); $x++) :
                    $chatTitle = '';
                    if (isset($chatsData[$x]['personal']['title'])) {
                        $chatTitle = $chatsData[$x]['personal']['title'];
                    } else {
                        $titleParts = [];
                        if (isset($chatsData[$x]['personal']['first_name'])) {
                            $titleParts[] = $chatsData[$x]['personal']['first_name'];
                        }
                        if (isset($chatsData[$x]['personal']['last_name'])) {
                            $titleParts[] = $chatsData[$x]['personal']['last_name'];
                        }
                        if (isset($chatsData[$x]['personal']['username'])) {
                            $titleParts[] = "(<a href='https://t.me/{$chatsData[$x]['personal']['username']}'>@{$chatsData[$x]['personal']['username']}</a>)";
                        }
                        $chatTitle = implode(' ', $titleParts);
                    }
                    $nickname = ' - ';
                    if (isset($chatsData[$x]['personal']['nickname'])) {
                        $nickname = $chatsData[$x]['personal']['nickname'];
                    }
                ?>
                    <tr>
                        <td><?= ($x + 1) ?>.</td>
                        <td title="Чат з"><?= $chatTitle ?></td>
                        <td title="Псевдонім" data-action-dblclick="account/set/nickname/form" data-cid="<?= $chatsData[$x]['uid'] ?>"><?= $nickname ?></td>
                        <td title="Остання активність"><?= date('d.m.Y H:i:s', $chatsData[$x]['data']['last_seems']) ?></td>
                    </tr>
                <? endfor; ?>
            </tbody>
        </table>
    <? else : ?>
        There is no data:)
    <? endif ?>
</section>