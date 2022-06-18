<section class='section common-form'>
    <form action="/page/edit/<?= $pageData['id'] ?>" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $texts['pageAddBlockTitle'] ?></h2>
        <div>
            <input type="text" name="title" value="<?= $pageData['title'] ?>" class="common-form__input title" placeholder="Title">
            <input type="hidden" name="short_name" value="<?= $pageData['short_name'] ?>">
        </div>
        <div><input type="text" name="subtitle" value="<?= $pageData['subtitle'] ?>" class="common-form__input subtitle" placeholder="Subtitle"></div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"><?= $pageData['html'] ?></div>
            </div>
        </div>
        <div class="common-form__button-place">
            <button type="submit" class="common-form__button"><?= $texts['pageAddSubmitTitle'] ?></button>
        </div>
    </form>
</section>