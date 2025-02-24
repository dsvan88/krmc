<section class='section common-form'>
    <form action="/page/edit/<?= $page['slug'] ?>" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div class="common-form__columns">
            <div class="common-form__column">
                <div class="common-form__row">
                    <input type="hidden" name="type" value="<?= $page['type'] ?>">
                    <input type="text" name="title" value="<?= $page['title'] ?>" class="common-form__input" placeholder="Title">
                </div>
                <div class="common-form__row">
                    <input type="text" name="subtitle" value="<?= $page['subtitle'] ?>" class="common-form__input" placeholder="Subtitle">
                </div>
            </div>
            <div class="common-form__column">
                <div class="common-form__row image__place">
                    <label for="news-logo-input-file" id="main-image-place" class="common-form__image-label">
                        <? if (empty($page['data']['logo'])) : ?>
                            Додати головне зображення
                        <? else : ?>
                            <img src="<?= $page['logo-link'] ?>" alt="main-image" class="common-form__image">
                        <? endif ?>
                    </label>
                    <input type="file" name="logo" value="" placeholder="Logo" class="common-form__input logo" id="news-logo-input-file" data-action-change="main-image-change" accept='image/*'>
                    <input type="hidden" name="main-image">
                </div>
                <div class="common-form__row">
                    <input type="text" name="logo-link" value="<?= $page['logo-link'] ?>" class="common-form__input" placeholder="Image's web link">
                </div>
            </div>
        </div>
        <details>
            <summary>Додаткові параметри:</summary>
            <fieldset>
                <legend>Додаткові налаштування сторінки:</legend>
                <div class="common-form__columns">
                    <div class="common-form__column">
                        <div class="common-form__row">
                            <label class="common-form__label">Зтислий опис:</label>
                        </div>
                        <div class="common-form__row">
                            <div class="editor-block" data-field="description">
                                <div class="toolbar-container"></div>
                                <div class="content-container">
                                    <div class="editor"><?= $page['description'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="common-form__column">
                        <div class="common-form__row"><label class="common-form__label" for="keywords">Ключові слова:</label><input type="text" id="keywords" name="keywords" value="<?= $page['keywords'] ?>" class="common-form__input" placeholder="Keywords"></div>
                        <div class="common-form__row"><label class="common-form__label" for="published_at">Дата публікації:</label><input type="datetime-local" id="published_at" name="published_at" value="<?= $page['published_at'] ?>" class="common-form__input" placeholder="Дата публікації"></div>
                        <div class="common-form__row"><label class="common-form__label" for="expired_at">Дійсна до:</label><input type="datetime-local" id="expired_at" name="expired_at" value="<?= $page['expired_at'] ?>" class="common-form__input" placeholder="Дійсна до"></div>
                    </div>
                </div>
            </fieldset>
        </details>
        <div class="common-form__row">
            <label class="common-form__label">Контент сторінки:</label>
        </div>
        <div class="common-form__row">
            <div class="editor-block" data-field="html">
                <div class="toolbar-container"></div>
                <div class="content-container">
                    <div class="editor"><?= $page['html'] ?></div>
                </div>
            </div>
        </div>
        <div class="common-form__button-place">
            <button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </form>
</section>