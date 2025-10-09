<section class='section form'>
    <form action="/chat/send" method="post" enctype="multipart/form-data" class="form__form">
        <div class="form__columns">
            <div class="form__column">
                <div class="form__row">
                    <h2 class="form__title"><?= $texts['blockTitle'] ?></h2>
                </div>
                <div class="form__row">
                    <label for="targets-list"><?= $texts['ChatListLabel'] ?>:</label>
                </div>
                <div class="form__row">
                    <select name="target" id="targets-list">
                        <option value="all"><?= $texts['sendAll'] ?></option>
                        <option value="groups"><?= $texts['sendGroups'] ?></option>
                        <option value="main" selected><?= $texts['sendMain'] ?></option>
                        <? for ($x = 0; $x < $chatsCount; $x++) : ?>
                            <option value="<?= $chats[$x]['uid'] ?>"><?= $chats[$x]['title'] ?></option>
                        <? endfor ?>
                    </select>
                </div>
            </div>
            <div class="form__column">
                <div class="form__row">
                    <? self::component('forms/image-modal') ?>
                </div>
            </div>
        </div>
        <div class="editor-block" data-field="html">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['submitTitle'] ?></button></div>
    </form>
</section>