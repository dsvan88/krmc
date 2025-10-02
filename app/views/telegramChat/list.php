<section class="section section-users-list">
    <? if (empty($chatsData)) : ?>
        There is no data yet:)
    <? else : ?>
        <table class="users-list" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Чат з</th>
                    <th>TelegramID</th>
                    <th>Остання активність</th>
                </tr>
            </thead>
            <tbody>
                <?
                for ($x = 0; $x < count($chatsData); $x++) :
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
                ?>
                    <tr>
                        <td><?= ($x + 1) ?>.</td>
                        <td title="Чат з">
                            <? if (!empty($chatsData[$x]['avatar'])) : ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($chatsData[$x]['avatar']) ?>"
                                    style="border-radius:50%;height:50px;width:auto" loading="lazy">
                            <? endif; ?>
                            <?= $chatTitle ?>
                        </td>
                        <td title=" TelegramID"><?= $chatsData[$x]['uid'] ?></td>
                        <td title="Остання активність"><?= date('d.m.Y H:i:s', $chatsData[$x]['data']['last_seems']) ?></td>
                    </tr>
                <? endfor; ?>
            </tbody>
        </table>
    <? endif ?>
</section>