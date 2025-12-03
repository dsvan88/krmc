<section class='section form'>
    <form action="/news/edit/<?= $newsData['id'] ?>" method="post" enctype="multipart/form-data" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <div><input type="hidden" name="type" value="<?= $newsData['type'] ?>"></div>
        <div class="form__item-logo">
            <label for="news-logo-input-file" id="main-image-place">
                <?= $newsData['logo'] ?>
            </label>
            <input type="file" name="logo" value="" placeholder="Logo" class="form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
            <input type="hidden" name="main-image">
        </div>
        <div><input type="text" name="title" value="<?= $newsData['title'] ?>" class="form__input title" placeholder="Title"></div>
        <div><input type="text" name="subtitle" value="<?= $newsData['subtitle'] ?>" class="form__input subtitle" placeholder="Subtitle"></div>
        <div class="editor-block" data-field="html">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"><?= $newsData['html'] ?></div>
            </div>
        </div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>