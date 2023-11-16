<section class='section common-form'>
    <form action="/chat/send" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $texts['blockTitle'] ?></h2>
        <div>
            <label for="targets-list"></label>
            <select name="target" id="targets-list">
                <option value="all"><?= $texts['sendAll'] ?></option>
                <option value="groups"><?= $texts['sendGroups'] ?></option>
                <option value="main"><?= $texts['sendMain'] ?></option>
                <? for ($x = 0; $x < count($chats); $x++) :
                    $chatTitle = '';
                    if (isset($chats[$x]['personal']['title'])) {
                        $chatTitle = $chats[$x]['personal']['title'];
                    } else {
                        $titleParts = [];
                        if (isset($chats[$x]['personal']['first_name'])) {
                            $titleParts[] = $chats[$x]['personal']['first_name'];
                        }
                        if (isset($chats[$x]['personal']['last_name'])) {
                            $titleParts[] = $chats[$x]['personal']['last_name'];
                        }
                        if (isset($chats[$x]['personal']['username'])) {
                            $titleParts[] = "(@{$chats[$x]['personal']['username']})";
                        }
                        $chatTitle = implode(' ', $titleParts);
                    }
                ?>
                    <option value="<?= $chats[$x]['uid'] ?>"><?= $chatTitle ?></option>
                <? endfor ?>
            </select>
        </div>
        <div>
            <label for="news-logo-input-file">
                Зображення
                <input type="file" name="logo" value="<?= $newsData['logo'] ?>" placeholder="Logo" class="common-form__input logo" id="news-logo-input-file">
            </label>
        </div>
        <div class="editor-block" data-field="html">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['submitTitle'] ?></button></div>
    </form>
</section>