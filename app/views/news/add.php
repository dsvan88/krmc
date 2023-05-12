<?/*<section class='section common-form'>
    <form action="/news/add" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
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
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>*/?>

<section class='section common-form'>
    <form action="/news/add" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div class="common-form__columns">
            <div class="common-form__column">
                <div class="common-form__row">
                    <label for="news-logo-input-file" id="main-image-place">
                        Додати головне зображення
                    </label>
                    <input type="file" name="logo" value="" placeholder="Logo" class="common-form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
                    <input type="hidden" name="main-image">
                </div>
                <div class="common-form__row"><input type="text" name="title" value="" class="common-form__input title" placeholder="Title"></div>
                <div class="common-form__row"><input type="text" name="subtitle" value="" class="common-form__input subtitle" placeholder="Subtitle"></div>
            </div>
            <div class="common-form__column">
                <div class="common-form__row"><label class="common-form__label" for="keywords">Ключові слова:</label><input type="text" id="keywords" name="keywords" value="" class="common-form__input" placeholder="Keywords"></div>
                <div class="common-form__row"><label class="common-form__label" for="published_at">Дата публікації:</label><input type="datetime-local" id="published_at" name="published_at" value="<?=date('Y-m-d').'T'.date('H:i')?>" class="common-form__input" placeholder="Дата публікації"></div>
                <div class="common-form__row"><label class="common-form__label" for="expired_at">Дійсна до:</label><input type="datetime-local" id="expired_at" name="expired_at" value="" class="common-form__input" placeholder="Дата публікації"></div>
            </div>
        </div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="common-form__button-place">
            <button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </form>
</section>