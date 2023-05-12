<section class='section common-form'>
    <form action="/news/edit/<?= $newsData['id'] ?>" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div><input type="hidden" name="type" value="<?= $newsData['type'] ?>"></div>
        <div class="common-form__item-logo">
            <label for="news-logo-input-file" id="main-image-place">
                <?= $newsData['logo'] ?>
            </label>
            <input type="file" name="logo" value="" placeholder="Logo" class="common-form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
            <input type="hidden" name="main-image">
        </div>
        <div><input type="text" name="title" value="<?= $newsData['title'] ?>" class="common-form__input title" placeholder="Title"></div>
        <div><input type="text" name="subtitle" value="<?= $newsData['subtitle'] ?>" class="common-form__input subtitle" placeholder="Subtitle"></div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"><?= $newsData['html'] ?></div>
            </div>
        </div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>