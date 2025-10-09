<section class='section form'>
    <form action="/news/add" method="post" enctype="multipart/form-data" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <div class="form__columns">
            <div class="form__column">
                <div class="form__row">
                    <label for="news-logo-input-file" id="main-image-place">
                        Додати головне зображення
                    </label>
                    <input type="file" name="logo" value="" placeholder="Logo" class="form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
                    <input type="hidden" name="main-image">
                </div>
                <div class="form__row"><input type="text" name="title" value="" class="form__input title" placeholder="Title"></div>
                <div class="form__row"><input type="text" name="subtitle" value="" class="form__input subtitle" placeholder="Subtitle"></div>
            </div>
            <div class="form__column">
                <div class="form__row"><label class="form__label" for="keywords">Ключові слова:</label><input type="text" id="keywords" name="keywords" value="" class="form__input" placeholder="Keywords"></div>
                <div class="form__row"><label class="form__label" for="published_at">Дата публікації:</label><input type="datetime-local" id="published_at" name="published_at" value="<?= date('Y-m-d') . 'T' . date('H:i') ?>" class="form__input" placeholder="Дата публікації"></div>
                <div class="form__row"><label class="form__label" for="expired_at">Дійсна до:</label><input type="datetime-local" id="expired_at" name="expired_at" value="" class="form__input" placeholder="Дата публікації"></div>
            </div>
        </div>
        <div class="editor-block" data-field="html">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="form__button-place">
            <button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </form>
</section>