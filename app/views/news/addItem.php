<section class='section common-form'>
    <form action="/news/add" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $texts['newsAddBlockTitle'] ?></h2>
        <div><input type="hidden" name="type" value="news"></div>
        <div>
            <label for="news-logo-input-file" id="main-image-place">
                Додати головне зображення
            </label>
            <input type="file" name="logo" value="" placeholder="Logo" class="common-form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
            <input type="hidden" name="main-image">
        </div>
        <div><input type="text" name="title" value="" class="common-form__input title" placeholder="Title"></div>
        <div><input type="text" name="subtitle" value="" class="common-form__input subtitle" placeholder="Subtitle"></div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['newsAddSubmitTitle'] ?></button></div>
    </form>
</section>